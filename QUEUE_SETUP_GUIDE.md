# دليل إعداد نظام Queues للدردشة

## 🎯 **لماذا Queues مهمة للدردشة؟**

### ✅ **المزايا:**

-   **سرعة فورية:** المستخدم يحصل على رد فوري
-   **موثوقية:** إعادة المحاولة التلقائية عند الفشل
-   **قابلية التوسع:** معالجة آلاف الرسائل في الخلفية
-   **فصل المسؤوليات:** المهام الفورية منفصلة عن البطيئة

### 📊 **مقارنة الأداء:**

| بدون Queues             | مع Queues          |
| ----------------------- | ------------------ |
| ⏱️ 2-5 ثواني            | ⏱️ 100-300ms       |
| 🔄 انقطاع عند الخطأ     | 🔄 استمرارية العمل |
| 💾 استهلاك عالي للذاكرة | 💾 استهلاك منخفض   |

## 🚀 **خطوات الإعداد:**

### 1. **إعداد قاعدة البيانات:**

```bash
# إنشاء جدول Jobs
php artisan queue:table

# إنشاء جدول Failed Jobs
php artisan queue:failed-table

# تشغيل Migrations
php artisan migrate
```

### 2. **إعداد ملف .env:**

```env
# Queue Configuration
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids

# Optional: Redis for better performance
# QUEUE_CONNECTION=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379
```

### 3. **تشغيل Queue Workers:**

```bash
# تشغيل worker واحد
php artisan queue:work

# تشغيل multiple workers
php artisan queue:work --queue=high,default,low

# تشغيل في الخلفية (Production)
php artisan queue:work --daemon

# تشغيل مع supervisor (مستحسن)
```

### 4. **إعداد Supervisor (مستحسن):**

```bash
# تثبيت Supervisor
sudo apt-get install supervisor

# إنشاء ملف config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

**محتوى الملف:**

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# إعادة تحميل Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 📋 **أنواع المهام:**

### 🔥 **مهام فورية (تتم فوراً):**

-   حفظ الرسالة في قاعدة البيانات
-   البث المباشر للرسالة
-   تحديث حالة "تم الإرسال"
-   إرجاع الرد للمستخدم

### ⏳ **مهام بطيئة (في الخلفية):**

-   إرسال إشعارات FCM
-   إرسال إشعارات قاعدة البيانات
-   معالجة الملفات المرفقة
-   إرسال إشعارات البريد الإلكتروني
-   تحديث الإحصائيات

## 🔧 **إدارة Queues:**

### **مراقبة Jobs:**

```bash
# عرض Jobs المعلقة
php artisan queue:monitor

# عرض Jobs الفاشلة
php artisan queue:failed

# إعادة تشغيل Job فاشل
php artisan queue:retry {id}

# إعادة تشغيل جميع Jobs الفاشلة
php artisan queue:retry all

# حذف Job فاشل
php artisan queue:forget {id}

# مسح جميع Jobs الفاشلة
php artisan queue:flush
```

### **مراقبة الأداء:**

```bash
# عرض إحصائيات Queue
php artisan queue:work --verbose

# مراقبة Logs
tail -f storage/logs/laravel.log
tail -f storage/logs/worker.log
```

## 🛠️ **استكشاف الأخطاء:**

### **مشاكل شائعة:**

1. **Jobs لا تعمل:**

```bash
# تأكد من تشغيل worker
php artisan queue:work

# تحقق من Logs
tail -f storage/logs/laravel.log
```

2. **Jobs تعلق:**

```bash
# إعادة تشغيل workers
php artisan queue:restart

# مسح cache
php artisan cache:clear
```

3. **أداء بطيء:**

```bash
# زيادة عدد workers
php artisan queue:work --queue=high,default,low

# استخدام Redis بدلاً من Database
```

## 📊 **أفضل الممارسات:**

### **1. تقسيم Queues حسب الأولوية:**

```php
// Jobs عالية الأولوية
ProcessMessageNotification::dispatch($message, $recipientId)->onQueue('high');

// Jobs عادية
ProcessMessageAttachment::dispatch($message)->onQueue('default');

// Jobs منخفضة الأولوية
UpdateStatistics::dispatch($userId)->onQueue('low');
```

### **2. إعداد Timeouts:**

```php
class ProcessMessageNotification implements ShouldQueue
{
    public $timeout = 30; // 30 ثانية
    public $tries = 3;    // 3 محاولات
    public $backoff = 60; // انتظار 60 ثانية بين المحاولات
}
```

### **3. معالجة الأخطاء:**

```php
public function failed(\Throwable $exception): void
{
    Log::error("Job failed", [
        'job' => get_class($this),
        'error' => $exception->getMessage()
    ]);
}
```

## 🎯 **النتائج المتوقعة:**

### **قبل Queues:**

-   ⏱️ وقت الاستجابة: 2-5 ثواني
-   🔄 انقطاع عند فشل الإشعارات
-   💾 استهلاك عالي للذاكرة

### **بعد Queues:**

-   ⏱️ وقت الاستجابة: 100-300ms
-   🔄 استمرارية العمل حتى مع فشل المهام
-   💾 استهلاك منخفض للذاكرة
-   📈 قابلية التوسع

## 📞 **الدعم:**

لأي استفسارات أو مشاكل في إعداد Queues، يرجى التواصل مع فريق التطوير.
