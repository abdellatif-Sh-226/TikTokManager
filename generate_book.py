#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
 generates Ur_backend.pdf - TikTok Manager Backend Guide in Arabic
"""

import arabic_reshaper
from bidi.algorithm import get_display
from fpdf import FPDF
import os

FONT_PATH = r"C:\Windows\Fonts\tahoma.ttf"
FONT_BOLD_PATH = r"C:\Windows\Fonts\tahomabd.ttf"
OUTPUT = r"C:\Users\wess\Desktop\Ur_backend.pdf"


class ArabicPDF(FPDF):
    def __init__(self):
        super().__init__()
        self.add_font("Tahoma", "", FONT_PATH, uni=True)
        self.add_font("Tahoma", "B", FONT_BOLD_PATH, uni=True)
        self.set_auto_page_break(auto=True, margin=25)

    def ar(self, text):
        reshaped = arabic_reshaper.reshape(text)
        return get_display(reshaped)

    def chapter_title(self, title, level=1):
        if level == 1:
            self.set_font("Tahoma", "B", 18)
            self.ln(10)
            self.cell(0, 12, self.ar(title), align="R", new_x="LMARGIN", new_y="NEXT")
            self.set_draw_color(0, 102, 204)
            self.set_line_width(0.8)
            self.line(self.w - self.l_margin, self.get_y(), self.r_margin, self.get_y())
            self.ln(8)
        elif level == 2:
            self.set_font("Tahoma", "B", 14)
            self.ln(6)
            self.cell(0, 10, self.ar(title), align="R", new_x="LMARGIN", new_y="NEXT")
            self.ln(4)
        elif level == 3:
            self.set_font("Tahoma", "B", 12)
            self.ln(4)
            self.cell(0, 8, self.ar(title), align="R", new_x="LMARGIN", new_y="NEXT")
            self.ln(3)

    def body_text(self, text):
        self.set_font("Tahoma", "", 11)
        self.multi_cell(0, 7, self.ar(text), align="R")
        self.ln(2)

    def code_block(self, code):
        self.set_font("Courier", "", 9)
        self.set_fill_color(240, 240, 240)
        self.set_draw_color(200, 200, 200)
        self.ln(3)
        x = self.get_x()
        w = self.w - self.l_margin - self.r_margin
        lines = code.strip().split("\n")
        for line in lines:
            # Strip Arabic from code comments for Courier font safety
            safe_line = line
            # Replace Arabic chars with ASCII equivalents for code blocks
            try:
                line.encode('latin-1')
            except UnicodeEncodeError:
                safe_line = line.encode('ascii', errors='replace').decode('ascii')
            self.cell(w, 5, "  " + safe_line, align="L", fill=True, new_x="LMARGIN", new_y="NEXT")
        self.ln(3)

    def bullet(self, text):
        self.set_font("Tahoma", "", 11)
        x = self.get_x()
        self.cell(8, 7, chr(8226), align="R")
        self.multi_cell(0, 7, self.ar(text), align="R")
        self.ln(1)

    def note_box(self, text):
        self.set_fill_color(255, 255, 220)
        self.set_draw_color(255, 200, 0)
        self.set_font("Tahoma", "", 10)
        y = self.get_y()
        self.ln(3)
        self.multi_cell(0, 6, self.ar(text), align="R", fill=True, border=1)
        self.ln(3)


pdf = ArabicPDF()
pdf.set_margin(20)

# ============================================================
# COVER PAGE
# ============================================================
pdf.add_page()
pdf.ln(50)
pdf.set_font("Tahoma", "B", 30)
pdf.cell(0, 20, pdf.ar("كتيب_backend"), align="C", new_x="LMARGIN", new_y="NEXT")
pdf.set_font("Tahoma", "", 16)
pdf.ln(8)
pdf.cell(0, 12, pdf.ar("دليل شامل لبناء الباك اند وإدارة TikTok"), align="C", new_x="LMARGIN", new_y="NEXT")
pdf.ln(5)
pdf.cell(0, 12, pdf.ar("TikTok Manager - StackFlow"), align="C", new_x="LMARGIN", new_y="NEXT")
pdf.ln(15)
pdf.set_font("Tahoma", "", 12)
pdf.cell(0, 8, pdf.ar(" Laravel 13  |  TikTok Content Posting API  |  Cloudinary  |  Sanctum Auth "), align="C", new_x="LMARGIN", new_y="NEXT")
pdf.ln(30)
pdf.set_font("Tahoma", "", 11)
pdf.cell(0, 8, pdf.ar("تاريخ الاصدار: يوليو 2026"), align="C", new_x="LMARGIN", new_y="NEXT")

# ============================================================
# TABLE OF CONTENTS
# ============================================================
pdf.add_page()
pdf.chapter_title("جدول المحتويات")
toc = [
    ("الفصل 1: نظرة عامة على المشروع", 3),
    ("الفصل 2: بنية المشروع", 4),
    ("الفصل 3: إعداد بيئة التطوير", 5),
    ("الفصل 4: قاعدة البيانات والنماذج", 7),
    ("الفصل 5: نظام المصادقة (Authentication)", 10),
    ("الفصل 6: دمج TikTok API - OAuth 2.0", 12),
    ("الفصل 7: Content Posting API - النشر على TikTok", 16),
    ("الفصل 8: رفع الفيديو إلى Cloudinary", 21),
    ("الفصل 9: نظام CORS ومشكلات الاتصال", 24),
    ("الفصل 10: ngrok - ربط السيرفر المحلي", 27),
    ("الفصل 11: نقاط النهاية (API Endpoints)", 29),
    ("الفصل 12: الواجهة الأمامية (Frontend)", 33),
    ("الفصل 13: حل المشاكل والتنقيح", 35),
    ("الفصل 14: ملخص الأخطاء والحلول", 40),
    ("الفصل 15: الخلاصة والخطوات القادمة", 45),
]
for title, page in toc:
    pdf.set_font("Tahoma", "", 12)
    dots = " . " * (60 - len(title))
    pdf.cell(0, 8, pdf.ar(f"{title}  {dots}  {page}"), align="R", new_x="LMARGIN", new_y="NEXT")

# ============================================================
# CHAPTER 1
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 1: نظرة عامة على المشروع")

pdf.chapter_title("ما هو TikTok Manager؟", 2)
pdf.body_text(
    "TikTok Manager هو تطبيق ويب يسمح للمستخدمين بإدارة حساباتهم على TikTok من خلال واجهة واحدة موحدة. "
    "يمكن للمستخدمين ربط حساباتهم باستخدام TikTok OAuth، ونشر الفيديوهات مباشرة من التطبيق، "
    "ومتابعة الإحصائيات مثل عدد المشاهدات والإعجابات والتعليقات."
)

pdf.chapter_title("التقنيات المستخدمة", 2)
pdf.bullet("Backend: Laravel 13 (PHP 8.3) - إطار عمل قوي ومتكامل")
pdf.bullet("Frontend: React 18 + TypeScript + Vite - واجهة مستخدم سريعة وحديثة")
pdf.bullet("Database: SQLite - قاعدة بيانات خفيفة للتطوير")
pdf.bullet("Auth: Laravel Sanctum - نظام مصادقة متكامل بالرموز المميزة (Tokens)")
pdf.bullet("TikTok API: Content Posting API v2 - للنشر والحصول على الإحصائيات")
pdf.bullet("Cloudinary: خدمة رفع وتخزين الفيديوهات والوسائط")
pdf.bullet("ngrok: لربط السيرفر المحلي بالإنترنت (مطلوب من TikTok للـ Redirect URI)")

pdf.chapter_title("كيف يعمل التطبيق؟", 2)
pdf.body_text(
    "1. المستخدم يضغط على زر Connect TikTok في التطبيق\n"
    "2. يتم توجيهه إلى صفحة إذن TikTok للمصادقة\n"
    "3. بعد الموافقة، يعود TikTok بـ Authorization Code\n"
    "4. الباك اند يستبدل الـ Code بـ Access Token وRefresh Token\n"
    "5. يتم حفظ التوكن في قاعدة البيانات مربوطة بحساب المستخدم\n"
    "6. المستخدم يستطيع الآن رؤية إحصائياته ونشر فيديوهات جديدة"
)

# ============================================================
# CHAPTER 2
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 2: بنية المشروع")

pdf.chapter_title("هيكل المجلدات", 2)
pdf.code_block("""TikTokManager/
  backend/                     # Laravel 13
    app/
      Http/
        Controllers/Api/       # API Controllers
          AuthController.php
          PostController.php
          DashboardController.php
          TikTokAuthController.php
        Middleware/
          CorsMiddleware.php
        Requests/
          StorePostRequest.php
      Models/
        User.php
        Post.php
        DailyStat.php
      Services/
        TikTokService.php      # TikTok API integration
        CloudinaryService.php  # Cloudinary file upload
    config/
      cors.php                 # CORS settings
      services.php             # TikTok credentials
    routes/
      api.php                  # API routes
    database/
      migrations/              # Database structure
    bootstrap/
      app.php                  # Middleware config
    .env                       # Environment variables
  front/                       # React + TypeScript
    src/
      pages/
      services/
      store/
    vite.config.ts             # Dev proxy config""")

pdf.chapter_title("مخطط التدفق العام", 2)
pdf.body_text(
    "التطبيق يتبع نمط Client-Server:\n\n"
    "العميل (React) يرسل طلبات HTTP إلى الباك اند (Laravel) عبر بورت 5173 -> 8000.\n"
    "Vite Dev Proxy يحول طلبات /api/* تلقائياً إلى localhost:8000.\n"
    "الباك اند يتواصل مع TikTok API و Cloudinary API.\n"
    "النتائج تعود إلى العميل بصيغة JSON."
)

# ============================================================
# CHAPTER 3
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 3: إعداد بيئة التطوير")

pdf.chapter_title("المتطلبات الأولية", 2)
pdf.bullet("PHP 8.3+ مع الامتدادات: openssl, mbstring, pdo_sqlite, curl, gd, zip")
pdf.bullet("Composer - مدير حزم PHP")
pdf.bullet("Node.js 18+ و npm - لتشغيل الواجهة الأمامية")
pdf.bullet("SQLite - قاعدة البيانات (مدمجة في PHP)")
pdf.bullet("ngrok - لربط السيرفر المحلي")
pdf.bullet("حساب TikTok Developer - للحصول على Client ID و Secret")
pdf.bullet("حساب Cloudinary - لرفع الفيديوهات")

pdf.chapter_title("إعداد الباك اند", 2)
pdf.code_block("""# 1. تثبيت Laravel
composer create-project laravel/laravel backend

# 2. إعداد ملف .env
cp .env.example .env
php artisan key:generate

# 3. تثبيت Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
php artisan migrate

# 4. تثبيت Guzzle
composer require guzzlehttp/guzzle

# 5. تشغيل السيرفر
php artisan serve --host=127.0.0.1 --port=8000""")

pdf.chapter_title("إعداد ملف .env", 2)
pdf.body_text("يجب إضافة هذه المتغيرات في ملف .env الخاص بالباك اند:")
pdf.code_block("""# TikTok App Credentials
TIKTOK_CLIENT_ID=sbaww9ng5x3ypwronl
TIKTOK_CLIENT_SECRET=usBgA3qYRZyZo18uSNPVgdH0imRcCamJ
TIKTOK_REDIRECT_URI=https://YOUR-NGROK-URL/api/auth/tiktok/callback
TIKTOK_SANDBOX_MODE=true

# Cloudinary Credentials
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

# Frontend URL (for CORS)
FRONTEND_URL=http://localhost:5173

# Database
DB_CONNECTION=sqlite""")

pdf.chapter_title("إعداد الواجهة الأمامية", 2)
pdf.code_block("""# 1. تثبيت المكتبات
cd front
npm install

# 2. إعداد .env
echo "VITE_API_URL=/api" > .env

# 3. تشغيل الخادم
npm run dev""")

pdf.note_box(
    "مهم: VITE_API_URL يجب أن يكون /api (مسار نسبي) وليس URL كامل، "
    "لأن Vite Proxy يحول الطلبات تلقائياً."
)

# ============================================================
# CHAPTER 4
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 4: قاعدة البيانات والنماذج")

pdf.chapter_title("مخطط قاعدة البيانات", 2)
pdf.body_text("يحتوي المشروع على 3 جداول رئيسية: users و posts و daily_stats.")

pdf.chapter_title("جدول المستخدمين (users)", 2)
pdf.code_block("""Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('avatar')->nullable();
    $table->string('tiktok_username')->nullable();
    // --- حقول TikTok الإضافية ---
    $table->string('tiktok_access_token')->nullable();
    $table->string('tiktok_refresh_token')->nullable();
    $table->timestamp('tiktok_token_expires_at')->nullable();
    $table->string('tiktok_open_id')->nullable()->unique();
    $table->rememberToken();
    $table->timestamps();
});""")

pdf.chapter_title("جدول المنشورات (posts)", 2)
pdf.code_block("""Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('description');
    $table->string('video_url')->nullable();
    $table->string('thumbnail_url')->nullable();
    $table->bigInteger('views')->default(0);
    $table->bigInteger('likes')->default(0);
    $table->bigInteger('comments')->default(0);
    $table->bigInteger('shares')->default(0);
    $table->string('status', 20)->default('draft');
    $table->string('tiktok_publish_id')->nullable();
    $table->string('tiktok_status', 30)->nullable();
    $table->timestamps();
});""")

pdf.chapter_title("جدول الإحصائيات اليومية (daily_stats)", 2)
pdf.code_block("""Schema::create('daily_stats', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->bigInteger('views')->default(0);
    $table->bigInteger('likes')->default(0);
    $table->bigInteger('comments')->default(0);
    $table->bigInteger('shares')->default(0);
    $table->timestamps();
    $table->unique(['user_id', 'date']);
});""")

pdf.chapter_title("نموذج المستخدم (User Model)", 2)
pdf.body_text(
    "نموذج User يرث من Authenticatum ويستخدم HasApiTokens من Sanctum. "
    "يحتوي على relationships مع Post و DailyStat."
)
pdf.code_block("""class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'avatar',
        'tiktok_username', 'tiktok_open_id',
        'tiktok_access_token', 'tiktok_refresh_token',
        'tiktok_token_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'tiktok_access_token', 'tiktok_refresh_token',
    ];

    protected function casts(): array {
        return [
            'tiktok_token_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function dailyStats() {
        return $this->hasMany(DailyStat::class);
    }
}""")

# ============================================================
# CHAPTER 5
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 5: نظام المصادقة (Authentication)")

pdf.chapter_title("نظام Sanctum Token", 2)
pdf.body_text(
    "Laravel Sanctum يوفر نظام مصادقة متكامل. في هذا المشروع نستخدم Personal Access Tokens "
    "لإنشاء توكن فريد لكل مستخدم بعد تسجيل الدخول عبر TikTok."
)

pdf.chapter_title("كيف يعمل التدفق؟", 2)
pdf.bullet("المستخدم يضغط Connect TikTok")
pdf.bullet("التطبيق يطلب URL المصادقة من الباك اند")
pdf.bullet("يتم توجيه المستخدم إلى TikTok للموافقة")
pdf.bullet("TikTok يرسل Authorization Code إلى Callback URL")
pdf.bullet("الباك اند يستبدل الـ Code بـ Access Token و Refresh Token")
pdf.bullet("يتم إنشاء أو تحديث المستخدم في قاعدة البيانات")
pdf.bullet("يتم إنشاء Sanctum Token وإرساله إلى الواجهة الأمامية")
pdf.bullet("الواجهة الأمامية تحفظ التوكن وتستخدمه في جميع الطلبات القادمة")

pdf.chapter_title("كود التوجيه (Routes)", 2)
pdf.code_block("""Route::prefix('auth/tiktok')->group(function () {
    Route::get('/redirect', [TikTokAuthController::class, 'redirect']);
    Route::get('/callback', [TikTokAuthController::class, 'callback']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});""")

pdf.chapter_title("TikTokAuthController - الدالة الرئيسية", 2)
pdf.body_text(
    "الدالة callback هي الأهم. تتلقى Authorization Code من TikTok، "
    "وتحوّله إلى Access Token، ثم تنشئ أو تحدث المستخدم في قاعدة البيانات."
)
pdf.code_block("""public function callback(Request $request)
{
    $code = $request->query('code');
    // 1. استبدال الـ Code بـ Token
    $tokenData = $this->tiktok->getAccessToken($code);
    $accessToken = $tokenData['access_token'];
    $openId = $tokenData['open_id'];

    // 2. جلب معلومات المستخدم من TikTok
    $userInfo = $this->tiktok->getUserInfo($accessToken, $openId);

    // 3. إنشاء أو تحديث المستخدم
    $user = User::updateOrCreate(
        ['tiktok_open_id' => $openId],
        [
            'name' => $userInfo['data']['user']['display_name'],
            'tiktok_access_token' => $accessToken,
            'tiktok_refresh_token' => $tokenData['refresh_token'],
            'tiktok_token_expires_at' => now()->addSeconds($tokenData['expires_in']),
        ]
    );

    // 4. إنشاء Sanctum Token
    $token = $user->createToken('api-token')->plainTextToken;

    // 5. إعادة التوجيه إلى الواجهة الأمامية مع التوكن
    return redirect()->away($frontendUrl . '/?token=' . $token);
}""")

pdf.note_box(
    "ملاحظة: المستخدم يُنشأ ببريد إلكتروني وهمي (openId@tiktok-user) "
    "وكلمة مرور عشوائية لأن المصادقة تتم بالكامل عبر TikTok."
)

# ============================================================
# CHAPTER 6
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 6: دمج TikTok API - OAuth 2.0")

pdf.chapter_title("خطوات المصادقة مع TikTok", 2)
pdf.body_text(
    "TikTok يستخدم بروتوكول OAuth 2.0 للمصادقة. هذا يعني أن المستخدم لا يدخل "
    "كلمة المرور في تطبيقك أبداً. بدلاً من ذلك، يتم توجيهه إلى TikTok للموافقة."
)

pdf.chapter_title("خطوة 1: إنشاء تطبيق TikTok Developer", 2)
pdf.bullet("اذهب إلى developers.tiktok.com")
pdf.bullet("أنشئ حساب Developer جديد")
pdf.bullet("أنشئ تطبيق جديد واحصل على Client Key و Client Secret")
pdf.bullet("أضف Redirect URI: https://YOUR-NGROK/api/auth/tiktok/callback")
pdf.bullet("فعّل Content Posting API من إعدادات التطبيق")
pdf.bullet("في وضع Sandbox، ضع حساب TikTok الخاص بك كحساب مطور محلي (Developer)")

pdf.chapter_title("خطوة 2: ربط ngrok", 2)
pdf.body_text(
    "TikTok يتطلب Redirect URI يبدأ بـ https. في بيئة التطوير، نستخدم ngrok "
    "لتحويل الرابط المحلي إلى رابط عام بـ HTTPS."
)
pdf.code_block("""# تشغيل ngrok
ngrok http 8000

# ستحصل على رابط مثل:
# https://shakily-sneezing-lunacy.ngrok-free.dev

# ضع هذا الرابط في:
# 1. TIKTOK_REDIRECT_URI في .env
# 2. APP_URL في .env""")

pdf.chapter_title("خطوة 3: توليد رابط المصادقة", 2)
pdf.code_block("""public function getAuthUrl(): string
{
    $params = http_build_query([
        'client_key' => $this->clientId,
        'scope' => 'user.info.basic,video.publish,
                     video.upload,user.info.profile,
                     user.info.stats,video.list',
        'redirect_uri' => $this->redirectUri,
        'response_type' => 'code',
        'state' => csrf_token(),
    ]);
    return "https://www.tiktok.com/v2/auth/authorize/?{$params}";
}""")

pdf.body_text(
    "المؤشرات (Scopes) المطلوبة:\n"
    "- user.info.basic: معلومات المستخدم الأساسية\n"
    "- video.publish: نشر الفيديوهات\n"
    "- video.upload: رفع الفيديوهات\n"
    "- user.info.profile: الملف الشخصي\n"
    "- user.info.stats: الإحصائيات\n"
    "- video.list: قائمة الفيديوهات"
)

pdf.chapter_title("خطوة 4: استبدال الـ Code بـ Token", 2)
pdf.code_block("""public function getAccessToken(string $code): array
{
    $response = $this->client->post('v2/oauth/token/', [
        'form_params' => [
            'client_key' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
        ],
    ]);
    return json_decode($response->getBody(), true);
    // يحتوي على: access_token, open_id, refresh_token,
    //            expires_in, scope
}""")

pdf.chapter_title("خطوة 5: تجديد التوكن", 2)
pdf.body_text(
    "Access Token ينتهي صلاحيته بعد فترة. Refresh Token يبقى صالحاً لفترة أطول. "
    "عندما ينتهي الـ Access Token، نستخدم Refresh Token للحصول على واحد جديد."
)
pdf.code_block("""public function refreshToken(string $refreshToken): array
{
    $response = $this->client->post('v2/oauth/token/', [
        'form_params' => [
            'client_key' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ],
    ]);
    return json_decode($response->getBody(), true);
}""")

pdf.note_box(
    "في وضع Sandbox، تنتهي صلاحية التوكن بعد 24 ساعة فقط. "
    "في الإنتاج، تنتهي بعد 30 يوماً."
)

# ============================================================
# CHAPTER 7
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 7: Content Posting API - النشر على TikTok")

pdf.chapter_title("نظرة عامة", 2)
pdf.body_text(
    "Content Posting API يسمح بنشر الفيديوهات مباشرة إلى TikTok. "
    "يوجد طريقتان للنشر:\n"
    "1. FILE_UPLOAD (PUSH_TO_SERVER): رفع الفيديو مباشرة إلى خوادم TikTok\n"
    "2. PULL_FROM_URL: إعطاء TikTok رابط الفيديو ليحمّله (يحتاج Domain موثوق)"
)

pdf.chapter_title("الطريقة 1: FILE_UPLOAD (المستخدمة في المشروع)", 2)
pdf.body_text(
    "هذه الطريقة هي الأنسب لوضع التطوير. نرفع الفيديو مباشرة من الخادم إلى TikTok."
)

pdf.chapter_title("الخطوة الأولى: استعلام معلومات المنشئ", 2)
pdf.code_block("""// endpoint: /v2/post/publish/creator_info/query/
public function queryCreatorInfo(string $accessToken): array
{
    $response = $this->client->post(
        'v2/post/publish/creator_info/query/',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]
    );
    return json_decode($response->getBody(), true);
    // يرد: privacy_level_options, comment_disabled,
    //       duet_disabled, stitch_disabled,
    //       max_video_post_duration_sec
}""")

pdf.chapter_title("الخطوة الثانية: بدء عملية النشر", 2)
pdf.code_block("""// endpoint: /v2/post/publish/video/init/
public function initPublish(string $accessToken, string $title,
    string $privacyLevel, bool $disableComment,
    int $videoSize): array
{
    $chunkSize = min(10000000, $videoSize); // 10MB max
    $totalChunkCount = ceil($videoSize / $chunkSize);

    $response = $this->client->post(
        'v2/post/publish/video/init/',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
            'json' => [
                'post_info' => [
                    'title' => $title,
                    'privacy_level' => $privacyLevel,
                    'disable_comment' => $disableComment,
                    'disable_duet' => false,
                    'disable_stitch' => false,
                ],
                'source_info' => [
                    'source' => 'FILE_UPLOAD',
                    'video_size' => $videoSize,
                    'chunk_size' => $chunkSize,
                    'total_chunk_count' => $totalChunkCount,
                ],
            ],
        ]
    );
    // يرد: publish_id, upload_url
    return json_decode($response->getBody(), true);
}""")

pdf.note_box(
    "مهم جداً: chunk_size يجب أن لا يتجاوز video_size. "
    "إذا كان الفيديو أصغر من 10MB، يجب ضبط chunk_size على حجم الفيديو الفعلي."
)

pdf.chapter_title("الخطوة الثالثة: رفع الفيديو", 2)
pdf.body_text("نستخدم PUT request مع Content-Range header لرفع الفيديو:")
pdf.code_block("""public function uploadVideo(string $uploadUrl, string $filePath,
    int $videoSize, int $chunkSize = 10000000): bool
{
    $totalChunkCount = ceil($videoSize / $chunkSize);
    $handle = fopen($filePath, 'rb');

    for ($i = 0; $i < $totalChunkCount; $i++) {
        $start = $i * $chunkSize;
        $end = min($start + $chunkSize - 1, $videoSize - 1);
        $chunkData = fread($handle, $end - $start + 1);

        $this->client->put($uploadUrl, [
            'headers' => [
                'Content-Type' => 'video/mp4',
                'Content-Range' => "bytes $start-$end/$videoSize",
            ],
            'body' => $chunkData,
        ]);
    }
    fclose($handle);
    return true;
}""")

pdf.chapter_title("الخطوة الرابعة: التحقق من حالة النشر", 2)
pdf.code_block("""// endpoint: /v2/post/publish/status/fetch/
public function getPublishStatus(string $accessToken,
    string $publishId): array
{
    $response = $this->client->post(
        'v2/post/publish/status/fetch/',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
            'json' => ['publish_id' => $publishId],
        ]
    );
    return json_decode($response->getBody(), true);
    // الحالات: PUBLISH_COMPLETE, FAILED, ERROR,
    //          PROCESSING
}""")

pdf.body_text(
    "في PostController، نتحقق من الحالة 5 مرات مع انتظار 3 ثوانٍ بين كل فحص."
)

# ============================================================
# CHAPTER 8
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 8: رفع الفيديو إلى Cloudinary")

pdf.chapter_title("لماذا Cloudinary؟", 2)
pdf.body_text(
    "Cloudinary هو خدمة مخصصة لرفع وتخزين وإدارة الملفات المرئية. "
    "نستخدمه لتخزين الفيديوهات محلياً حتى لو فشل النشر على TikTok. "
    "يقدم أيضاً ميزات مثل التحويل التلقائي للصيغ وتحسين الجودة."
)

pdf.chapter_title("إعداد حساب Cloudinary", 2)
pdf.bullet("أنشئ حساب مجاني على cloudinary.com")
pdf.bullet("خذ Cloud Name و API Key و API Secret من Dashboard")
pdf.bullet("ضعهم في ملف .env")

pdf.chapter_title("CloudinaryService", 2)
pdf.body_text(
    "الخدمة تستخدم Guzzle HTTP Client لرفع الملفات بصيغة multipart. "
    "لا نستخدم SDK الرسمي، بل نستخدم الـ API مباشرة."
)
pdf.code_block("""class CloudinaryService
{
    public function upload(UploadedFile $file, string $folder): string
    {
        $timestamp = time();
        $signature = $this->generateSignature([
            'folder' => $folder,
            'timestamp' => $timestamp,
        ]);

        $response = $this->client->post(
            "https://api.cloudinary.com/v1_1/{$this->cloudName}/auto/upload",
            [
                'multipart' => [
                    ['name' => 'file',
                     'contents' => fopen($file->getRealPath(), 'r'),
                     'filename' => $file->getClientOriginalName()],
                    ['name' => 'folder', 'contents' => $folder],
                    ['name' => 'timestamp', 'contents' => (string) $timestamp],
                    ['name' => 'api_key', 'contents' => $this->apiKey],
                    ['name' => 'signature', 'contents' => $signature],
                ],
            ]
        );

        $result = json_decode($response->getBody(), true);
        return $result['secure_url'];
    }

    private function generateSignature(array $params): string
    {
        ksort($params);
        $stringToSign = http_build_query($params);
        return sha1($stringToSign . $this->apiSecret);
    }
}""")

pdf.note_box(
    "Cloudinary يتطلب timeout عالياً (120 ثانية) لأن رفع الفيديوهات قد يستغرق وقتاً طويلاً. "
    "أيضاً يجب ضبط PHP max_execution_time إلى 300 ثانية لتجنب أخطاء Timeout."
)

# ============================================================
# CHAPTER 9
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 9: نظام CORS ومشكلات الاتصال")

pdf.chapter_title("ما هو CORS؟", 2)
pdf.body_text(
    "CORS (Cross-Origin Resource Sharing) هو آلية أمان في المتصفحات تمنع "
    "المواقع من طلب موارد من نطاقات مختلفة. في حالتنا، الواجهة الأمامية "
    "تعمل على localhost:5173 والباك اند على localhost:8000 - هما نطاقان مختلفان."
)

pdf.chapter_title("الحل المستخدم: Vite Dev Proxy", 2)
pdf.body_text(
    "بدلاً من استخدام middleware معقد، نستخدم Vite Dev Server Proxy: "
    "كل طلب إلى /api/* يتم تحويله تلقائياً إلى localhost:8000."
)
pdf.code_block("""// front/vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
});""")

pdf.chapter_title("الحل الإضافي: CorsMiddleware", 2)
pdf.body_text(
    "للحالات التي لا يغطيها الـ Proxy (مثل callbacks من TikTok)، "
    "أنشئنا CorsMiddleware مخصصاً:"
)
pdf.code_block("""class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->addHeaders(response('', 204), $request);
        }
        return $this->addHeaders($next($request), $request);
    }

    private function addHeaders(Response $response, Request $request): Response
    {
        $origin = $request->header('Origin');
        $allowedOrigins = config('cors.allowed_origins');
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        return $response;
    }
}""")

pdf.chapter_title("تسجيل الـ Middleware في bootstrap/app.php", 2)
pdf.code_block("""// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->replace(
        \\Illuminate\\Http\\Middleware\\HandleCors::class,
        \\App\\Http\\Middleware\\CorsMiddleware::class
    );
})""")

pdf.note_box(
    "سبب استبدال HandleCors هو أن middleware Laravel الافتراضي "
    "لم يكن يضيف headers بشكل صحيح في بعض الحالات."
)

# ============================================================
# CHAPTER 10
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 10: ngrok - ربط السيرفر المحلي")

pdf.chapter_title("لماذا نحتاج ngrok؟", 2)
pdf.body_text(
    "TikTok يتطلب Redirect URI يبدأ بـ https:// في بيئة التطوير. "
    "السيرفر المحلي يعمل على http://localhost:8000. نحتاج أداة تحوّل "
    "الرابط المحلي إلى رابط عام بـ HTTPS."
)

pdf.chapter_title("كيف يعمل ngrok؟", 2)
pdf.body_text(
    "1. ngrok يتصل بخوادم ngrok على الإنترنت\n"
    "2. ينشئ رابط عام مثل https://abc123.ngrok-free.app\n"
    "3. أي طلب يذهب إلى هذا الرابط يتم تحويله إلى localhost:8000\n"
    "4. هذا يحل مشكلة HTTPS + مشكلة الوصول من أي مكان"
)

pdf.chapter_title("خطوات الإعداد", 2)
pdf.code_block("""# 1. تحميل ngrok من ngrok.com
# 2. إنشاء حساب مجاني
# 3. تشغيل ngrok
ngrok http 8000

# 4. ستحصل على رابط مثل:
# https://shakily-sneezing-lunacy.ngrok-free.dev

# 5. ضع الرابط في .env:
# APP_URL=https://shakily-sneezing-lunacy.ngrok-free.dev
# TIKTOK_REDIRECT_URI=https://shakily-sneezing-lunacy.ngrok-free.dev/api/auth/tiktok/callback""")

pdf.note_box(
    "ملاحظة: الرابط يتغير كل مرة تشغل فيها ngrok في وضع المجاني. "
    "يجب تحديث TIKTOK_REDIRECT_URI في كل مرة في .env وفي إعدادات تطبيق TikTok Developer."
)

pdf.chapter_title("مشاكل شائعة مع ngrok", 2)
pdf.bullet("الرابط ينتهي عند إغلاق ngrok: أعد تشغيله وحدّث الإعدادات")
pdf.bullet("صفحة ngrok warning: اضغط Continue to Site")
pdf.bullet("Timeout: تأكد من أن الباك اند يعمل على البورت الصحيح")
pdf.bullet("SSL Error: تأكد من استخدام https وليس http في الإعدادات")

# ============================================================
# CHAPTER 11
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 11: نقاط النهاية (API Endpoints)")

pdf.chapter_title("نقاط المصادقة", 2)
pdf.code_block("""POST /api/login
  Body: { email, password }
  Response: { user, token }

GET /api/auth/tiktok/redirect
  Response: { url: "https://tiktok.com/v2/auth/authorize/..." }

GET /api/auth/tiktok/callback?code=xxx&state=xxx
  Response: Redirect to frontend with token

POST /api/logout
  Headers: Authorization: Bearer {token}
  Response: { message: "Logged out" }

GET /api/user
  Headers: Authorization: Bearer {token}
  Response: { id, name, email, ... }""")

pdf.chapter_title("نقاط Dashboard", 2)
pdf.code_block("""GET /api/dashboard/stats
  Headers: Authorization: Bearer {token}
  Response: {
    data: {
      followers: 40,
      views: 15576,
      likes: 168,
      comments: 5,
      shares: 3,
      avatar: "https://...",
      displayName: "No_One",
      username: "noone226noone"
    }
  }

GET /api/dashboard/daily-stats
  Headers: Authorization: Bearer {token}
  Response: {
    data: [
      { date: "2026-07-16", views: 15464, likes: 159, ... },
      { date: "2026-07-17", views: 15473, likes: 162, ... }
    ]
  }""")

pdf.chapter_title("نقاط المنشورات", 2)
pdf.code_block("""GET /api/posts
  Headers: Authorization: Bearer {token}
  Response: {
    data: [
      {
        id: 1,
        description: "My video",
        videoUrl: "https://res.cloudinary.com/...",
        views: 100,
        likes: 10,
        status: "published"
      }
    ]
  }

POST /api/posts
  Headers: Authorization: Bearer {token}
  Content-Type: multipart/form-data
  Body: {
    description: "My video",
    hashtags: "#funny",
    video: File(video.mp4),
    privacy_level: "SELF_ONLY",
    disable_comment: "0",
    publish_to_tiktok: "1"
  }
  Response: {
    data: { ...post, tiktokStatus: { publish_id, status } }
  }

DELETE /api/posts/{post}
  Headers: Authorization: Bearer {token}
  Response: { message: "Deleted" }""")

# ============================================================
# CHAPTER 12
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 12: الواجهة الأمامية (Frontend)")

pdf.chapter_title("بنية المشروع", 2)
pdf.body_text(
    "الواجهة الأمامية مبنية بـ React 18 مع TypeScript و Vite. "
    "تستخدم Zustand لإدارة الحالة و Axios للطلبات HTTP."
)

pdf.chapter_title("مجلد الخدمات (services/)", 2)
pdf.bullet("api.ts: إعداد Axios مع Interceptors + logging")
pdf.bullet("posts.ts: دوال CRUD للمنشورات")

pdf.chapter_title("api.ts - الإعداد الأساسي", 2)
pdf.code_block("""const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { 'Content-Type': 'application/json' },
});

// Request interceptor - يضيف التوكن
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor - يعالج الأخطاء
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);""")

pdf.chapter_title("posts.ts - دوال المنشورات", 2)
pdf.code_block("""export const postsService = {
  async getAll() {
    const response = await api.get('/posts');
    // يتعامل مع: string, array, object responses
    const data = response.data;
    if (Array.isArray(data)) return data.filter(Boolean);
    if (data?.data) return Array.isArray(data.data) ? data.data.filter(Boolean) : [];
    return [];
  },

  async create(formData: FormData) {
    const response = await api.post('/posts', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },
};""")

pdf.chapter_title("المخزن (Store) - Zustand", 2)
pdf.code_block("""// store/index.ts
export const usePostsStore = create((set, get) => ({
  posts: [],
  loading: false,

  addPost: async (formData) => {
    set({ loading: true });
    try {
      const response = await postsService.create(formData);
      const post = response?.data;
      if (post && post.id) {
        set((state) => ({
          posts: [post, ...state.posts].filter(Boolean),
          loading: false,
        }));
      }
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  },
}));""")

# ============================================================
# CHAPTER 13
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 13: حل المشاكل والتنقيح")

pdf.chapter_title("مشكلة: CORS Blocked", 2)
pdf.body_text("السبب: المتصفح يمنع الطلبات من localhost:5173 إلى localhost:8000.")
pdf.body_text("الحل: Vite Proxy + CorsMiddleware (انظر الفصل 9).")

pdf.chapter_title("مشكلة: File Upload Fails", 2)
pdf.body_text("السبب: PHP upload_max_filesize الافتراضي = 2MB.")
pdf.body_text("الحل: تعديل php.ini:")
pdf.code_block("""; C:\\php.ini
file_uploads = On
upload_max_filesize = 50M
post_max_size = 55M
upload_tmp_dir = "C:\\Users\\wess\\AppData\\Local\\Temp"
max_execution_time = 300
default_socket_timeout = 300""")

pdf.chapter_title("مشكلة: Maximum Execution Time Exceeded", 2)
pdf.body_text(
    "السبب: رفع الفيديو إلى Cloudinary يستغرق وقتاً طويلاً. "
    "php artisan serve لا يحترم php.ini max_execution_time دائماً."
)
pdf.body_text("الحل: إضافة set_time_limit(300) في بداية الدالة:")
pdf.code_block("""public function store(StorePostRequest $request): JsonResponse
{
    set_time_limit(300);  // 5 دقائق
    // ... بقية الكود
}""")

pdf.chapter_title("مشكلة: chunk_size is invalid", 2)
pdf.body_text(
    "السبب: chunk_size (10MB) أكبر من حجم الفيديو الفعلي (مثلاً 2MB)."
)
pdf.body_text("الحل: تقييد chunk_size بحجم الفيديو:")
pdf.code_block("""$chunkSize = min($chunkSize, $videoSize);
$totalChunkCount = ceil($videoSize / $chunkSize);""")

pdf.chapter_title("مشكلة: url_ownership_unverified", 2)
pdf.body_text(
    "السبب: استخدام PULL_FROM_URL في وضع Sandbox. "
    "TikTok يتطلب Domain موثوقاً للسحب من رابط."
)
pdf.body_text("الحل: استخدام FILE_UPLOAD بدلاً من PULL_FROM_URL.")

pdf.chapter_title("مشكلة: unaudited_client_can_only_post_to_private_accounts", 2)
pdf.body_text(
    "السبب: التطبيق في وضع Sandbox (غير معتمد). TikTok يسمح فقط بالنشر "
    "إلى حسابات خاصة."
)
pdf.body_text(
    "الحل: اجعل حساب TikTok الخاص بك خاصة:\n"
    "TikTok App -> Profile -> Settings -> Privacy -> Private Account = ON"
)

pdf.chapter_title("مشكلة: queryCreatorInfo sends empty JSON body", 2)
pdf.body_text(
    "السبب: tiktok->queryCreatorInfo كان يرسل body فارغ مع Content-Type: application/json."
)
pdf.body_text(
    "الحل: أزلنا الـ json body والـ Content-Type header لأن الـ endpoint لا يتطلب body."
)

# ============================================================
# CHAPTER 14
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 14: ملخص الأخطاء والحلول")

pdf.chapter_title("جدول الأخطاء الشائعة", 2)

errors = [
    ("CORS: blocked by CORS policy", "Vite Proxy + CorsMiddleware", "الفصل 9"),
    ("upload_max_filesize exceeded", "تعديل php.ini + set_time_limit()", "الفصل 13"),
    ("chunk_size is invalid", "min(chunkSize, videoSize)", "الفصل 13"),
    ("url_ownership_unverified", "استخدم FILE_UPLOAD", "الفصل 7"),
    ("unaudited_client...", "اجعل الحساب خاصة", "الفصل 13"),
    ("queryCreatorInfo error 400", "أزل body من الطلب", "الفصل 6"),
    ("ngrok connection refused", "تأكد ngrok + backend يعملان", "الفصل 10"),
    ("Token expired", "استخدم refreshToken()", "الفصل 6"),
    ("Connection timed out", "زِد timeout إلى 120s", "الفصل 13"),
    ("Maximum execution time 30s", "set_time_limit(300)", "الفصل 13"),
]

pdf.set_font("Tahoma", "B", 10)
pdf.set_fill_color(0, 102, 204)
pdf.set_text_color(255, 255, 255)
pdf.cell(60, 8, pdf.ar("الحل"), align="C", fill=True, border=1)
pdf.cell(60, 8, pdf.ar("السبب"), align="C", fill=True, border=1)
pdf.cell(60, 8, pdf.ar("الخطأ"), align="C", fill=True, border=1)
pdf.set_text_color(0, 0, 0)
pdf.ln()

for i, (error, fix, chapter) in enumerate(errors):
    pdf.set_font("Tahoma", "", 9)
    fill = i % 2 == 0
    if fill:
        pdf.set_fill_color(240, 240, 255)
    pdf.cell(60, 7, pdf.ar(chapter), align="C", fill=fill, border=1)
    pdf.cell(60, 7, pdf.ar(fix), align="C", fill=fill, border=1)
    pdf.cell(60, 7, pdf.ar(error), align="C", fill=fill, border=1)
    pdf.ln()

pdf.ln(5)
pdf.chapter_title("سجل الخطوات أثناء التطوير", 2)
pdf.body_text(
    "تتبعنا للمشاكل خطوة بخطوة:\n\n"
    "1. CORS error -> أنشأنا CorsMiddleware ثم Vite Proxy\n"
    "2. File upload 2MB -> عدّلنا php.ini upload_max_filesize\n"
    "3. Token expired -> أضفنا refreshToken() في controllers\n"
    "4. queryCreatorInfo 400 -> أزلنا body من الطلب\n"
    "5. url_ownership_unverified -> تحولنا من PULL_FROM_URL إلى FILE_UPLOAD\n"
    "6. chunk_size invalid -> أضفنا min(chunkSize, videoSize)\n"
    "7. unaudited_client -> طلبنا جعل الحساب خاصة\n"
    "8. Maximum execution time -> أضفنا set_time_limit(300)\n"
)

# ============================================================
# CHAPTER 15
# ============================================================
pdf.add_page()
pdf.chapter_title("الفصل 15: الخلاصة والخطوات القادمة")

pdf.chapter_title("ملخص ما بنينا", 2)
pdf.body_text(
    "بنينا تطبيق إدارة TikTok كامل يشمل:\n\n"
    "1. مصادقة آمنة عبر TikTok OAuth 2.0\n"
    "2. رفع الفيديوهات إلى Cloudinary للتخزين\n"
    "3. نشر الفيديوهات مباشرة إلى TikTok عبر Content Posting API\n"
    "4. متابعة الإحصائيات (المشاهدات، الإعجابات، التعليقات، المشاركات)\n"
    "5. واجهة مستخدم حديثة وسريعة\n"
    "6. نظام CORS يعمل بشكل صحيح\n"
    "7. معالجة شاملة للأخطاء مع logging مفصل"
)

pdf.chapter_title("الخطوات القادمة", 2)
pdf.bullet("تقديم التطبيق للاعتماد من TikTok (من وضع Sandbox إلى Production)")
pdf.bullet("إضافة جدولة النشر (نشر الفيديوهات في أوقات محددة)")
pdf.bullet("إضافة دعم نشر صور متعددة")
pdf.bullet("تحسين واجهة المستخدم مع إضافة Dark Mode")
pdf.bullet("إضافة نظام إشعارات")
pdf.bullet("تحسين الأداء بـ Queue و Background Jobs لرفع الفيديوهات")
pdf.bullet("إضافة اختبارات تلقائية (Unit + Feature Tests)")
pdf.bullet("النشر على خادم حقيقي (AWS/DigitalOcean)")

pdf.chapter_title("نصائح مهمة", 2)
pdf.bullet("لا تحفظ Secrets في الكود - استخدم .env دائماً")
pdf.bullet("تفعيل logging المفصل يوفر ساعات من البحث عن الأخطاء")
pdf.bullet("استخدم Vite Proxy في التطوير بدلاً من تعديل CORS بشكل يدوى")
pdf.bullet("TikTok Sandbox له قيود كثيرة - تأكد من قراءة التوثيق")
pdf.bullet("اضبط timeout مناسب للصور والفيديوهات الكبيرة")
pdf.bullet("افحص siempre الكود بلغة PHP قبل التشغيل: php -l file.php")

pdf.ln(20)
pdf.set_font("Tahoma", "B", 16)
pdf.cell(0, 12, pdf.ar("انتهى الكتاب"), align="C", new_x="LMARGIN", new_y="NEXT")
pdf.ln(5)
pdf.set_font("Tahoma", "", 12)
pdf.cell(0, 8, pdf.ar("بالتوفيق والنجاح!"), align="C", new_x="LMARGIN", new_y="NEXT")
pdf.ln(10)
pdf.set_font("Tahoma", "", 10)
pdf.cell(0, 8, pdf.ar("StackFlow - TikTok Manager Backend Guide"), align="C", new_x="LMARGIN", new_y="NEXT")

# ============================================================
# SAVE
# ============================================================
pdf.output(OUTPUT)
print(f"PDF saved to: {OUTPUT}")
print(f"Pages: {pdf.pages_count}")
