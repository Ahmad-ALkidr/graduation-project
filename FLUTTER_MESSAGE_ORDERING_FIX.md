# 📱 إصلاح ترتيب الرسائل في الواجهة

## 🔍 **المشكلة:**

الرسائل تصل بشكل صحيح لكن تظهر في أماكن مختلفة (أعلى وأسفل) بدلاً من أن تكون مرتبة من الأقدم للأحدث.

## 🛠️ **الحل:**

### 1. **إصلاح ترتيب الرسائل في ChatBloc:**

```dart
class ChatBloc extends Bloc<ChatEvent, ChatState> {
  // ... باقي الكود

  void _onMessageReceived(MessageReceived event, Emitter<ChatState> emit) {
    if (state is MessagesLoaded) {
      final currentState = state as MessagesLoaded;
      final List<MessageModel> currentMessages = List.from(currentState.messages);

      // إضافة الرسالة الجديدة
      currentMessages.add(event.message);

      // ترتيب الرسائل من الأقدم للأحدث (مثل واتساب)
      currentMessages.sort((a, b) => a.createdAt.compareTo(b.createdAt));

      emit(MessagesLoaded(messages: currentMessages));

      // تمرير للأسفل تلقائياً
      _scrollToBottom();
    }
  }

  void _onMessagesLoaded(MessagesLoaded event, Emitter<ChatState> emit) {
    // ترتيب الرسائل عند التحميل الأولي
    final sortedMessages = List<MessageModel>.from(event.messages);
    sortedMessages.sort((a, b) => a.createdAt.compareTo(b.createdAt));

    emit(MessagesLoaded(messages: sortedMessages));
  }

  void _scrollToBottom() {
    // تمرير للأسفل في الواجهة
    // يمكن استخدام ScrollController
  }
}
```

### 2. **إصلاح ترتيب الرسائل في الواجهة:**

```dart
class ChatPage extends StatefulWidget {
  final int conversationId;

  ChatPage({required this.conversationId});

  @override
  _ChatPageState createState() => _ChatPageState();
}

class _ChatPageState extends State<ChatPage> {
  final ScrollController _scrollController = ScrollController();

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    if (_scrollController.hasClients) {
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOut,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('المحادثة')),
      body: Column(
        children: [
          Expanded(
            child: BlocBuilder<ChatBloc, ChatState>(
              builder: (context, state) {
                if (state is MessagesLoaded) {
                  // ترتيب الرسائل من الأقدم للأحدث
                  final sortedMessages = List<MessageModel>.from(state.messages);
                  sortedMessages.sort((a, b) => a.createdAt.compareTo(b.createdAt));

                  return ListView.builder(
                    controller: _scrollController,
                    itemCount: sortedMessages.length,
                    itemBuilder: (context, index) {
                      final message = sortedMessages[index];
                      return MessageWidget(message: message);
                    },
                  );
                }
                return Center(child: CircularProgressIndicator());
              },
            ),
          ),
          // حقل إرسال الرسالة
          TextField(
            decoration: InputDecoration(
              hintText: 'اكتب رسالتك...',
              suffixIcon: IconButton(
                icon: Icon(Icons.send),
                onPressed: _sendMessage,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _sendMessage() {
    // كود إرسال الرسالة
  }
}
```

### 3. **إصلاح ترتيب الرسائل في ListView:**

```dart
// في MessageWidget أو في الواجهة الرئيسية
class MessagesList extends StatelessWidget {
  final List<MessageModel> messages;
  final ScrollController scrollController;

  const MessagesList({
    Key? key,
    required this.messages,
    required this.scrollController,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // ترتيب الرسائل من الأقدم للأحدث
    final sortedMessages = List<MessageModel>.from(messages);
    sortedMessages.sort((a, b) => a.createdAt.compareTo(b.createdAt));

    return ListView.builder(
      controller: scrollController,
      itemCount: sortedMessages.length,
      itemBuilder: (context, index) {
        final message = sortedMessages[index];
        return MessageWidget(message: message);
      },
    );
  }
}
```

### 4. **إصلاح ترتيب الرسائل في API Response:**

```dart
// في Repository أو Service
class ChatRepository {
  Future<List<MessageModel>> getMessages(int conversationId) async {
    try {
      final response = await dio.get('/conversations/$conversationId/messages');

      if (response.statusCode == 200) {
        final List<dynamic> data = response.data['data'];
        final messages = data.map((json) => MessageModel.fromJson(json)).toList();

        // ترتيب الرسائل من الأقدم للأحدث
        messages.sort((a, b) => a.createdAt.compareTo(b.createdAt));

        return messages;
      }
      throw Exception('Failed to load messages');
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
```

### 5. **إصلاح ترتيب الرسائل عند إضافة رسالة جديدة:**

```dart
// في ChatBloc
void _onSendMessage(SendMessage event, Emitter<ChatState> emit) async {
  if (state is MessagesLoaded) {
    final currentState = state as MessagesLoaded;
    final List<MessageModel> currentMessages = List.from(currentState.messages);

    try {
      // إرسال الرسالة
      final result = await chatRepository.sendMessage(
        conversationId: event.conversationId,
        content: event.content,
      );

      result.fold(
        (failure) {
          emit(ChatError(failure.message));
        },
        (newMessage) {
          // إضافة الرسالة الجديدة
          currentMessages.add(newMessage);

          // ترتيب الرسائل من الأقدم للأحدث
          currentMessages.sort((a, b) => a.createdAt.compareTo(b.createdAt));

          emit(MessagesLoaded(messages: currentMessages));

          // تمرير للأسفل
          _scrollToBottom();
        },
      );
    } catch (e) {
      emit(ChatError('Failed to send message: $e'));
    }
  }
}
```

### 6. **إصلاح ترتيب الرسائل في Model:**

```dart
class MessageModel {
  final int id;
  final String content;
  final DateTime createdAt;
  final UserModel sender;
  final int conversationId;

  MessageModel({
    required this.id,
    required this.content,
    required this.createdAt,
    required this.sender,
    required this.conversationId,
  });

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    return MessageModel(
      id: json['id'],
      content: json['content'],
      createdAt: DateTime.parse(json['created_at']), // تأكد من تنسيق التاريخ
      sender: UserModel.fromJson(json['sender']),
      conversationId: json['conversation_id'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'content': content,
      'created_at': createdAt.toIso8601String(),
      'sender': sender.toJson(),
      'conversation_id': conversationId,
    };
  }
}
```

## 🎯 **النقاط المهمة:**

### ✅ **ما يجب التأكد منه:**

1. **تنسيق التاريخ:** تأكد من أن `created_at` يتم تحليله بشكل صحيح
2. **ترتيب الرسائل:** استخدم `sort()` في كل مرة يتم فيها تحديث القائمة
3. **تمرير للأسفل:** استخدم `ScrollController` للتمرير التلقائي
4. **ترتيب API:** تأكد من أن API يرجع الرسائل مرتبة

### 🔧 **خطوات التطبيق:**

1. **تطبيق التعديلات في ChatBloc**
2. **تطبيق التعديلات في الواجهة**
3. **التأكد من تنسيق التاريخ في Model**
4. **اختبار ترتيب الرسائل**

### 🎉 **النتيجة المتوقعة:**

-   ✅ الرسائل مرتبة من الأقدم للأحدث
-   ✅ الرسائل الجديدة تظهر في الأسفل
-   ✅ تمرير تلقائي للأسفل عند استلام رسالة جديدة
-   ✅ ترتيب صحيح مثل واتساب وتطبيقات المحادثات الأخرى
