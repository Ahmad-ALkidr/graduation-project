# 📱 ConversationsBloc مصحح

```dart
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shamunity/apis/chat/conversation.dart';
import 'package:shamunity/core/helpers/toast.dart';
import 'package:shamunity/core/service/services_locator.dart';
import 'package:shamunity/core/widgets/loading_dialog_widget.dart';
import 'package:shamunity/logic/conversation%20bloc/conversation_event.dart';
import 'package:shamunity/logic/conversation%20bloc/conversation_state.dart';
import 'package:shamunity/models/conversation_model.dart';
import 'package:shamunity/routes/extension.dart';

class ConversationsBloc extends Bloc<ConversationsEvent, ConversationsState> {
  final Conversation conversationRepository;
  final ConversationPusher conversationPusher;
  StreamSubscription? _conversationSubscription;
  bool _isListening = false;
  int? _currentUserId;

  ConversationsBloc({
    required this.conversationRepository,
    required this.conversationPusher,
  }) : super(ConversationsInitial()) {
    on<FetchConversations>(_onFetchConversations);
    on<ConversationUpdated>(_onConversationUpdated);
    on<CreateConversation>(_createConversation);
    on<StartListeningToConversations>(_onStartListeningToConversations);
    on<StopListeningToConversations>(_onStopListeningToConversations);
    debugPrint("🚀 ConversationsBloc: تم إنشاؤه بنجاح");
  }

  // بدء الاستماع للمحادثات
  Future<void> _onStartListeningToConversations(
    StartListeningToConversations event,
    Emitter<ConversationsState> emit,
  ) async {
    if (_isListening && _currentUserId == event.userId) {
      debugPrint("ℹ️ ConversationsBloc: الاستماع مُفعل بالفعل للمستخدم ${event.userId}");
      return;
    }

    debugPrint("🎧 ConversationsBloc: بدء الاستماع للمحادثات للمستخدم ${event.userId}");

    try {
      // إيقاف الاستماع السابق إذا كان موجود
      if (_isListening) {
        _stopListening();
      }

      _currentUserId = event.userId;

      // تهيئة Pusher إذا لم يكن مُهيأ
      if (!conversationPusher.isConnected) {
        await conversationPusher.init();
      }

      // الاشتراك في قناة المستخدم
      await conversationPusher.subscribeToUserConversations(event.userId);

      // بدء الاستماع
      _startListeningToConversations();

      debugPrint("✅ ConversationsBloc: تم بدء الاستماع بنجاح");
    } catch (e) {
      debugPrint("❌ ConversationsBloc: فشل في بدء الاستماع: $e");
    }
  }

  // إيقاف الاستماع للمحادثات
  Future<void> _onStopListeningToConversations(
    StopListeningToConversations event,
    Emitter<ConversationsState> emit,
  ) async {
    debugPrint("🛑 ConversationsBloc: إيقاف الاستماع للمحادثات");
    _stopListening();
  }

  // دالة منفصلة لبدء الاستماع للمحادثات
  void _startListeningToConversations() {
    if (_isListening) {
      debugPrint("ℹ️ ConversationsBloc: الاستماع مُفعل بالفعل");
      return;
    }

    debugPrint("🎧 ConversationsBloc: إعداد مستمع المحادثات...");

    _conversationSubscription = conversationPusher.conversationStream.listen(
      (conversation) {
        debugPrint("📨 ConversationsBloc: استلام تحديث محادثة جديدة:");
        debugPrint("   🆔 ID: ${conversation.id}");
        debugPrint("   👤 المشارك: ${conversation.participant.name}");
        debugPrint("   💬 آخر رسالة: ${conversation.lastMessage?.content ?? 'لا توجد رسالة'}");

        // إضافة المحادثة المحدثة للـ BLoC
        add(ConversationUpdated(conversation));
      },
      onError: (error) {
        debugPrint("❌ ConversationsBloc: خطأ في مجرى المحادثات: $error");
      },
      onDone: () {
        debugPrint("🔚 ConversationsBloc: انتهى مجرى المحادثات");
        _isListening = false;
      },
    );

    _isListening = true;
    debugPrint("✅ ConversationsBloc: تم تفعيل مستمع المحادثات");
  }

  Future<void> _onFetchConversations(
    FetchConversations event,
    Emitter<ConversationsState> emit,
  ) async {
    debugPrint("📥 ConversationsBloc: بدء جلب المحادثات للمستخدم ${event.userId}");

    emit(ConversationsLoading());

    try {
      // جلب المحادثات من API
      final result = await conversationRepository.getConversations();

      result.fold(
        (failure) {
          debugPrint("❌ ConversationsBloc: فشل في جلب المحادثات: ${failure.message}");
          emit(ConversationsError(failure.message));
        },
        (data) {
          debugPrint("✅ ConversationsBloc: تم جلب ${data.length} محادثة بنجاح");
          emit(ConversationsLoaded(conversations: data));

          // بدء الاستماع للمحادثات بعد التحميل
          add(StartListeningToConversations(userId: event.userId));
        },
      );
    } catch (e) {
      debugPrint("❌ ConversationsBloc: خطأ في جلب المحادثات: $e");
      emit(ConversationsError("فشل في جلب المحادثات: $e"));
    }
  }

  void _onConversationUpdated(
    ConversationUpdated event,
    Emitter<ConversationsState> emit,
  ) {
    debugPrint("🔄 ConversationsBloc: تحديث المحادثة");
    debugPrint("   🆔 محادثة ID: ${event.updatedConversation.id}");

    // تحقق من أن الحالة الحالية هي قائمة محملة
    if (state is ConversationsLoaded) {
      final currentState = state as ConversationsLoaded;
      final List<ConversationModel> currentList = List.from(currentState.conversations);

      // ابحث عن المحادثة التي يجب تحديثها
      final int index = currentList.indexWhere((convo) => convo.id == event.updatedConversation.id);

      if (index != -1) {
        debugPrint("✅ ConversationsBloc: تم العثور على المحادثة في القائمة، تحديث...");

        // 1. أزل النسخة القديمة من القائمة
        currentList.removeAt(index);

        // 2. أضف النسخة المحدثة في بداية القائمة (لتصعد للأعلى)
        currentList.insert(0, event.updatedConversation);

        // 3. أصدر الحالة الجديدة مع القائمة المحدثة
        emit(ConversationsLoaded(conversations: currentList));
        debugPrint("✅ ConversationsBloc: تم تحديث المحادثة وإعادة ترتيب القائمة");
      } else {
        debugPrint("ℹ️ ConversationsBloc: محادثة جديدة غير موجودة في القائمة");

        // إذا كانت محادثة جديدة غير موجودة في القائمة
        // أضفها في بداية القائمة
        currentList.insert(0, event.updatedConversation);
        emit(ConversationsLoaded(conversations: currentList));
        debugPrint("✅ ConversationsBloc: تم إضافة محادثة جديدة للقائمة");
      }
    } else {
      debugPrint("⚠️ ConversationsBloc: الحالة الحالية ليست ConversationsLoaded");

      // إذا لم تكن الحالة محملة، أنشئ قائمة جديدة بالمحادثة
      emit(ConversationsLoaded(conversations: [event.updatedConversation]));
      debugPrint("✅ ConversationsBloc: تم إنشاء قائمة جديدة مع المحادثة");
    }
  }

  void _createConversation(
    CreateConversation event,
    Emitter<ConversationsState> emit,
  ) async {
    debugPrint("🔄 ConversationsBloc: إنشاء محادثة جديدة مع المستخدم ${event.userId}");

    BuildContext? context = SingleInstanceService.context;
    showDialog(
      context: context!,
      builder: (BuildContext context) => const LoadingDialogWidget(),
    );

    try {
      final result = await conversationRepository.createConversation(event.userId);

      result.fold(
        (failure) {
          debugPrint("❌ ConversationsBloc: فشل في إنشاء المحادثة: ${failure.message}");
          context.pop();
          emit(ConversationsError(failure.message));
          Toast().error(context, failure.message);
        },
        (conversation) {
          debugPrint("✅ ConversationsBloc: تم إنشاء المحادثة بنجاح - ID: ${conversation.id}");

          context.pop();
          context.pushNamed('/userChatScreen', arguments: conversation);
          emit(ConversationsAddLoaded(conversation));
        },
      );
    } catch (e) {
      debugPrint("❌ ConversationsBloc: خطأ في إنشاء المحادثة: $e");
      context.pop();
      emit(ConversationsError("فشل في إنشاء المحادثة: $e"));
      Toast().error(context, "فشل في إنشاء المحادثة");
    }
  }

  // إيقاف الاستماع
  void _stopListening() {
    if (_conversationSubscription != null) {
      _conversationSubscription?.cancel();
      _conversationSubscription = null;
      _isListening = false;
      debugPrint("🛑 ConversationsBloc: تم إيقاف الاستماع للمحادثات");
    }
  }

  // إعادة تشغيل الاستماع بعد العودة من المحادثة
  Future<void> restartListeningAfterReturn(int userId) async {
    debugPrint("🔄 ConversationsBloc: إعادة تشغيل الاستماع بعد العودة من الشات...");

    try {
      // التحقق من حالة الاتصال
      if (!conversationPusher.isConnected) {
        debugPrint("🔌 ConversationsBloc: Pusher غير متصل، إعادة الاتصال...");
        await conversationPusher.reconnect();
      }

      // التحقق من الاشتراك في القناة
      final userChannelName = 'private-App.Models.User.$userId';
      if (!conversationPusher.subscribedChannels.contains(userChannelName)) {
        debugPrint("📡 ConversationsBloc: إعادة الاشتراك في قناة المستخدم...");
        await conversationPusher.subscribeToUserConversations(userId);
      }

      // التأكد من تشغيل المستمع
      if (!_isListening) {
        debugPrint("👂 ConversationsBloc: إعادة تشغيل المستمع...");
        _startListeningToConversations();
      }

      debugPrint("✅ ConversationsBloc: تم إعادة تشغيل الاستماع بنجاح");
    } catch (e) {
      debugPrint("❌ ConversationsBloc: فشل في إعادة تشغيل الاستماع: $e");
    }
  }

  @override
  Future<void> close() {
    debugPrint("🔚 ConversationsBloc: إغلاق البلوك...");
    _stopListening();
    conversationPusher.disconnect();
    debugPrint("✅ ConversationsBloc: تم الإغلاق بنجاح");
    return super.close();
  }

  // دوال مساعدة للوصول إلى المعلومات
  bool get isListeningToConversations => _isListening;
  bool get isPusherConnected => conversationPusher.isConnected;
  Set<String> get subscribedChannels => conversationPusher.subscribedChannels;
  int? get currentUserId => _currentUserId;
}
```

## 2. إضافة Events جديدة:

```dart
// في conversation_event.dart
abstract class ConversationsEvent {}

class FetchConversations extends ConversationsEvent {
  final int userId;
  FetchConversations({required this.userId});
}

class StartListeningToConversations extends ConversationsEvent {
  final int userId;
  StartListeningToConversations({required this.userId});
}

class StopListeningToConversations extends ConversationsEvent {}

class ConversationUpdated extends ConversationsEvent {
  final ConversationModel updatedConversation;
  ConversationUpdated(this.updatedConversation);
}

class CreateConversation extends ConversationsEvent {
  final int userId;
  CreateConversation({required this.userId});
}
```
