# 📱 مثال مبسط للبث اللحظي في Flutter

## المشكلة الأساسية

مبرمج Flutter يحتاج إلى إدارة الاستماع للبث اللحظي بشكل صحيح عند التنقل بين الصفحات.

## الحل المبسط

### 1. إضافة Dependencies

```yaml
# pubspec.yaml
dependencies:
    pusher_channels_flutter: ^2.0.0
    flutter_local_notifications: ^15.1.0
```

### 2. إنشاء Service مبسط

```dart
// lib/services/simple_chat_service.dart

import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'dart:convert';

class SimpleChatService {
  static final SimpleChatService _instance = SimpleChatService._internal();
  factory SimpleChatService() => _instance;
  SimpleChatService._internal();

  PusherChannelsFlutter? _pusher;
  String? _currentUserId;
  String? _currentConversationId;

  // تهيئة الخدمة
  Future<void> initialize() async {
    _pusher = PusherChannelsFlutter.getInstance();

    await _pusher!.init(
      apiKey: "YOUR_PUSHER_KEY",
      cluster: "YOUR_PUSHER_CLUSTER",
    );

    await _pusher!.connect();
  }

  // بدء الاستماع للمحادثات (في صفحة قائمة المحادثات)
  Future<void> listenToConversations(String userId) async {
    _currentUserId = userId;

    await _pusher!.subscribe(
      channelName: "private-App.Models.User.$userId",
      onEvent: (event) {
        if (event.eventName == "conversation.updated") {
          print("Conversation updated: ${event.data}");
          // هنا يمكن إضافة كود لتحديث قائمة المحادثات
        }
      },
    );
  }

  // بدء الاستماع لمحادثة معينة (في صفحة المحادثة)
  Future<void> listenToConversation(String conversationId) async {
    _currentConversationId = conversationId;

    await _pusher!.subscribe(
      channelName: "private-chat.private.$conversationId",
      onEvent: (event) {
        if (event.eventName == "PrivateMessageSent") {
          print("New message: ${event.data}");
          // هنا يمكن إضافة كود لإضافة الرسالة الجديدة
        }
      },
    );
  }

  // إيقاف الاستماع لمحادثة معينة (عند الخروج من المحادثة)
  Future<void> stopListeningToConversation() async {
    if (_currentConversationId != null) {
      await _pusher!.unsubscribe(channelName: "private-chat.private.$_currentConversationId");
      _currentConversationId = null;
    }
  }

  // إيقاف الاستماع للمحادثات (عند الخروج من صفحة الدردشات)
  Future<void> stopListeningToConversations() async {
    if (_currentUserId != null) {
      await _pusher!.unsubscribe(channelName: "private-App.Models.User.$_currentUserId");
      _currentUserId = null;
    }
  }
}
```

### 3. استخدام الخدمة في الصفحات

#### صفحة قائمة المحادثات:

```dart
// lib/pages/conversations_list_page.dart

class ConversationsListPage extends StatefulWidget {
  @override
  _ConversationsListPageState createState() => _ConversationsListPageState();
}

class _ConversationsListPageState extends State<ConversationsListPage> {
  final SimpleChatService _chatService = SimpleChatService();

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
    final userId = "123"; // احصل على معرف المستخدم من التطبيق
    await _chatService.listenToConversations(userId);
  }

  Future<void> _stopListening() async {
    await _chatService.stopListeningToConversations();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('المحادثات')),
      body: ListView.builder(
        itemCount: conversations.length,
        itemBuilder: (context, index) {
          return ListTile(
            title: Text(conversations[index].title),
            onTap: () {
              // الانتقال لصفحة المحادثة
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => ConversationPage(
                    conversationId: conversations[index].id,
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
```

#### صفحة المحادثة:

```dart
// lib/pages/conversation_page.dart

class ConversationPage extends StatefulWidget {
  final int conversationId;

  ConversationPage({required this.conversationId});

  @override
  _ConversationPageState createState() => _ConversationPageState();
}

class _ConversationPageState extends State<ConversationPage> {
  final SimpleChatService _chatService = SimpleChatService();

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
    await _chatService.listenToConversation(widget.conversationId.toString());
  }

  Future<void> _stopListening() async {
    await _chatService.stopListeningToConversation();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('المحادثة')),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              itemCount: messages.length,
              itemBuilder: (context, index) {
                return MessageWidget(message: messages[index]);
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

### 4. تهيئة الخدمة في main.dart

```dart
// lib/main.dart

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // تهيئة خدمة البث اللحظي
  await SimpleChatService().initialize();

  runApp(MyApp());
}
```

## النقاط المهمة

### ✅ ما يجب فعله:

1. **في صفحة قائمة المحادثات:**

    - استدعاء `listenToConversations()` في `initState()`
    - استدعاء `stopListeningToConversations()` في `dispose()`

2. **في صفحة المحادثة:**
    - استدعاء `listenToConversation()` في `initState()`
    - استدعاء `stopListeningToConversation()` في `dispose()`

### ❌ ما يجب تجنبه:

1. **عدم إيقاف الاستماع عند الخروج من الصفحة**
2. **الاستماع لنفس القناة مرتين**
3. **نسيان معرف المستخدم أو معرف المحادثة**

## اختبار سريع

1. افتح صفحة قائمة المحادثات
2. افتح محادثة معينة
3. اخرج من المحادثة
4. أرسل رسالة من مصدر آخر
5. يجب أن تظهر الإشعارات في صفحة قائمة المحادثات

## استكشاف الأخطاء

### إذا لم تعمل الإشعارات:

1. تحقق من console في Flutter
2. تأكد من تشغيل Laravel Echo Server
3. تحقق من معرف المستخدم
4. تأكد من إيقاف الاستماع بشكل صحيح

### رسائل التصحيح المفيدة:

```dart
print("Started listening to conversations for user: $userId");
print("Started listening to conversation: $conversationId");
print("Stopped listening to conversation: $conversationId");
print("Stopped listening to conversations");
```
