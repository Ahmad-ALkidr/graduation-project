# 📱 إعداد البث اللحظي للمحادثات في Flutter

## المشكلة

-   ✅ الإشعارات تصل عند الدخول لصفحة الدردشات
-   ❌ عند الدخول لمحادثة معينة والخروج منها، لا تصل التحديثات
-   ✅ التحديثات تصل فقط عند تحديث الصفحة

## الحل

### 1. إعداد Pusher في Flutter

أضف dependency في `pubspec.yaml`:

```yaml
dependencies:
    pusher_channels_flutter: ^2.0.0
    flutter_local_notifications: ^15.1.0
```

### 2. إنشاء Service للبث اللحظي

```dart
// lib/services/chat_realtime_service.dart

import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class ChatRealtimeService {
  static final ChatRealtimeService _instance = ChatRealtimeService._internal();
  factory ChatRealtimeService() => _instance;
  ChatRealtimeService._internal();

  PusherChannelsFlutter? _pusher;
  FlutterLocalNotificationsPlugin? _notifications;

  // متغير لتتبع حالة الاستماع
  bool _isListening = false;
  String? _currentUserId;

  // تهيئة الخدمة
  Future<void> initialize() async {
    // تهيئة Pusher
    _pusher = PusherChannelsFlutter.getInstance();

    try {
      await _pusher!.init(
        apiKey: "YOUR_PUSHER_KEY",
        cluster: "YOUR_PUSHER_CLUSTER",
      );

      await _pusher!.connect();
      print("Pusher connected successfully");
    } catch (e) {
      print("Pusher connection failed: $e");
    }

    // تهيئة الإشعارات المحلية
    _notifications = FlutterLocalNotificationsPlugin();
    const initializationSettingsAndroid = AndroidInitializationSettings('@mipmap/ic_launcher');
    const initializationSettingsIOS = DarwinInitializationSettings();
    const initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: initializationSettingsIOS,
    );
    await _notifications!.initialize(initializationSettings);
  }

  // بدء الاستماع للمحادثات (عند الدخول لصفحة الدردشات)
  Future<void> startListeningToConversations(String userId) async {
    if (_isListening && _currentUserId == userId) return;

    _currentUserId = userId;
    _isListening = true;

    try {
      // الاستماع لتحديثات المحادثات
      await _pusher!.subscribe(
        channelName: "private-App.Models.User.$userId",
        onEvent: (event) {
          print("Received event: ${event.eventName}");

          if (event.eventName == "conversation.updated") {
            _handleConversationUpdate(event.data);
          }
        },
      );

      print("Started listening to conversations for user: $userId");
    } catch (e) {
      print("Failed to start listening: $e");
    }
  }

  // بدء الاستماع لمحادثة معينة (عند الدخول لمحادثة)
  Future<void> startListeningToConversation(String conversationId) async {
    try {
      await _pusher!.subscribe(
        channelName: "private-chat.private.$conversationId",
        onEvent: (event) {
          print("Received message event: ${event.eventName}");

          if (event.eventName == "PrivateMessageSent") {
            _handleNewMessage(event.data);
          }
        },
      );

      print("Started listening to conversation: $conversationId");
    } catch (e) {
      print("Failed to start listening to conversation: $e");
    }
  }

  // إيقاف الاستماع لمحادثة معينة (عند الخروج من محادثة)
  Future<void> stopListeningToConversation(String conversationId) async {
    try {
      await _pusher!.unsubscribe(channelName: "private-chat.private.$conversationId");
      print("Stopped listening to conversation: $conversationId");
    } catch (e) {
      print("Failed to stop listening to conversation: $e");
    }
  }

  // إيقاف الاستماع للمحادثات (عند الخروج من صفحة الدردشات)
  Future<void> stopListeningToConversations() async {
    if (!_isListening) return;

    try {
      await _pusher!.unsubscribe(channelName: "private-App.Models.User.$_currentUserId");
      _isListening = false;
      _currentUserId = null;
      print("Stopped listening to conversations");
    } catch (e) {
      print("Failed to stop listening to conversations: $e");
    }
  }

  // معالجة تحديث المحادثة
  void _handleConversationUpdate(String data) {
    try {
      final conversationData = json.decode(data);
      final conversation = conversationData['conversation'];

      // تحديث قائمة المحادثات في التطبيق
      _updateConversationList(conversation);

      // إظهار إشعار إذا كان المستخدم ليس في المحادثة الحالية
      if (!_isInCurrentConversation(conversation['id'])) {
        _showNotification(conversation);
      }
    } catch (e) {
      print("Error handling conversation update: $e");
    }
  }

  // معالجة رسالة جديدة
  void _handleNewMessage(String data) {
    try {
      final messageData = json.decode(data);
      final message = messageData['message'];

      // إضافة الرسالة الجديدة للدردشة
      _addNewMessage(message);
    } catch (e) {
      print("Error handling new message: $e");
    }
  }

  // تحديث قائمة المحادثات
  void _updateConversationList(Map<String, dynamic> conversation) {
    // استخدم Provider أو Bloc أو أي state management
    // لتحديث قائمة المحادثات في التطبيق
    // مثال:
    // context.read<ConversationProvider>().updateConversation(conversation);
  }

  // إضافة رسالة جديدة
  void _addNewMessage(Map<String, dynamic> message) {
    // استخدم Provider أو Bloc لتحديث الرسائل في المحادثة الحالية
    // مثال:
    // context.read<MessageProvider>().addMessage(message);
  }

  // التحقق من أن المستخدم في المحادثة الحالية
  bool _isInCurrentConversation(int conversationId) {
    // تحقق من معرف المحادثة الحالية
    // يمكن استخدام متغير عام أو Provider
    return false; // قم بتعديل هذا حسب منطق التطبيق
  }

  // إظهار إشعار محلي
  Future<void> _showNotification(Map<String, dynamic> conversation) async {
    final latestMessage = conversation['latest_message'];
    final sender = latestMessage['sender'];

    const androidDetails = AndroidNotificationDetails(
      'chat_channel',
      'Chat Notifications',
      channelDescription: 'Notifications for new chat messages',
      importance: Importance.high,
      priority: Priority.high,
    );

    const iosDetails = DarwinNotificationDetails();

    const details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _notifications!.show(
      conversation['id'],
      sender['first_name'],
      latestMessage['content'],
      details,
      payload: json.encode({
        'type': 'conversation',
        'conversation_id': conversation['id'],
      }),
    );
  }

  // إغلاق الاتصال
  Future<void> dispose() async {
    await stopListeningToConversations();
    await _pusher?.disconnect();
  }
}
```

### 3. استخدام الخدمة في التطبيق

#### في صفحة قائمة المحادثات:

```dart
// lib/pages/conversations_page.dart

class ConversationsPage extends StatefulWidget {
  @override
  _ConversationsPageState createState() => _ConversationsPageState();
}

class _ConversationsPageState extends State<ConversationsPage> {
  final ChatRealtimeService _chatService = ChatRealtimeService();

  @override
  void initState() {
    super.initState();
    _startListening();
  }

  @override
  void dispose() {
    _stopListening();
    super.dispose();
  }

  Future<void> _startListening() async {
    final userId = getCurrentUserId(); // احصل على معرف المستخدم
    await _chatService.startListeningToConversations(userId);
  }

  Future<void> _stopListening() async {
    await _chatService.stopListeningToConversations();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('المحادثات')),
      body: // قائمة المحادثات
    );
  }
}
```

#### في صفحة المحادثة الفردية:

```dart
// lib/pages/conversation_page.dart

class ConversationPage extends StatefulWidget {
  final int conversationId;

  ConversationPage({required this.conversationId});

  @override
  _ConversationPageState createState() => _ConversationPageState();
}

class _ConversationPageState extends State<ConversationPage> {
  final ChatRealtimeService _chatService = ChatRealtimeService();

  @override
  void initState() {
    super.initState();
    _startListening();
  }

  @override
  void dispose() {
    _stopListening();
    super.dispose();
  }

  Future<void> _startListening() async {
    await _chatService.startListeningToConversation(widget.conversationId.toString());
  }

  Future<void> _stopListening() async {
    await _chatService.stopListeningToConversation(widget.conversationId.toString());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('المحادثة')),
      body: // محتوى المحادثة
    );
  }
}
```

### 4. إعداد الإشعارات المحلية

#### في `main.dart`:

```dart
// lib/main.dart

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // تهيئة خدمة البث اللحظي
  await ChatRealtimeService().initialize();

  runApp(MyApp());
}
```

### 5. معالجة النقر على الإشعارات

```dart
// في main.dart أو في service منفصل

void _handleNotificationTap(String payload) {
  if (payload != null) {
    final data = json.decode(payload);
    if (data['type'] == 'conversation') {
      // الانتقال لصفحة المحادثة
      Navigator.pushNamed(
        context,
        '/conversation',
        arguments: data['conversation_id'],
      );
    }
  }
}
```

## الميزات المضافة

### ✅ استماع مستمر للمحادثات

-   الاستماع لتحديثات قائمة المحادثات عند الدخول لصفحة الدردشات
-   الاستماع للرسائل الجديدة عند الدخول لمحادثة معينة

### ✅ إدارة ذكية للاستماع

-   إيقاف الاستماع لمحادثة معينة عند الخروج منها
-   إيقاف الاستماع للمحادثات عند الخروج من صفحة الدردشات

### ✅ إشعارات محلية

-   إشعارات فورية عند استلام رسائل جديدة
-   إمكانية النقر على الإشعار للانتقال للمحادثة

### ✅ تحديث تلقائي للواجهة

-   تحديث قائمة المحادثات لحظيًا
-   إضافة الرسائل الجديدة تلقائيًا

## اختبار النظام

1. افتح تطبيق Flutter
2. ادخل لصفحة الدردشات
3. افتح محادثة معينة
4. أرسل رسالة من تطبيق آخر أو من الويب
5. يجب أن تظهر الرسالة تلقائيًا
6. اخرج من المحادثة
7. أرسل رسالة أخرى
8. يجب أن تظهر إشعارات

## استكشاف الأخطاء

### إذا لم تعمل الإشعارات:

1. تحقق من إعدادات Pusher
2. تأكد من تشغيل Laravel Echo Server
3. تحقق من معرف المستخدم
4. تأكد من صلاحيات الإشعارات في التطبيق

### رسائل الخطأ الشائعة:

-   `Pusher connection failed`: تحقق من إعدادات Pusher
-   `Channel subscription failed`: تحقق من معرف المستخدم
-   `Notification permission denied`: تحقق من صلاحيات الإشعارات

## ملاحظات مهمة

-   تأكد من إضافة معرف المستخدم الصحيح
-   اختبر النظام على أجهزة مختلفة
-   تأكد من عمل النظام في الخلفية
-   اختبر الإشعارات عند إغلاق التطبيق
