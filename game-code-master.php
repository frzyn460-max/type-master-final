<?php
/**
 * ====================================
 * Code Master Game
 * بازی تایپ کد برنامه‌نویسی
 * ====================================
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$user = getCurrentUser();
$currentLang = getCurrentLanguage();
$currentTheme = getCurrentTheme();

// دریافت بهترین امتیاز
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT MAX(score) as best_score, MAX(wpm) as best_wpm, MAX(accuracy) as best_accuracy 
        FROM game_scores 
        WHERE user_id = :user_id AND game_type = 'code-master'";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$bestStats = $stmt->fetch(PDO::FETCH_ASSOC);

// کدهای برنامه‌نویسی
$codeSamples = [
    'html' => [
        'easy' => [
            ['code' => '<h1>Hello World</h1>', 'desc_fa' => 'تگ سرتیتر اصلی صفحه', 'desc_en' => 'Main heading tag'],
            ['code' => '<p>Welcome to coding</p>', 'desc_fa' => 'تگ پاراگراف برای متن', 'desc_en' => 'Paragraph tag for text'],
            ['code' => '<a href="#">Click here</a>', 'desc_fa' => 'لینک قابل کلیک', 'desc_en' => 'Clickable link'],
            ['code' => '<img src="photo.jpg" alt="Photo">', 'desc_fa' => 'نمایش تصویر', 'desc_en' => 'Display image'],
            ['code' => '<div class="container"></div>', 'desc_fa' => 'کانتینر برای گروه‌بندی', 'desc_en' => 'Container for grouping']
        ],
        'medium' => [
            ['code' => '<form action="submit.php" method="POST">', 'desc_fa' => 'فرم ارسال داده به سرور', 'desc_en' => 'Form to submit data'],
            ['code' => '<input type="text" name="username" required>', 'desc_fa' => 'فیلد ورودی الزامی', 'desc_en' => 'Required input field'],
            ['code' => '<button type="submit">Send</button>', 'desc_fa' => 'دکمه ارسال فرم', 'desc_en' => 'Form submit button'],
            ['code' => '<ul><li>Item 1</li><li>Item 2</li></ul>', 'desc_fa' => 'لیست نقطه‌ای', 'desc_en' => 'Unordered list'],
            ['code' => '<table><tr><td>Data</td></tr></table>', 'desc_fa' => 'جدول داده', 'desc_en' => 'Data table']
        ],
        'hard' => [
            ['code' => '<div class="row"><div class="col-md-6"></div></div>', 'desc_fa' => 'سیستم گرید Bootstrap', 'desc_en' => 'Bootstrap grid system'],
            ['code' => '<nav class="navbar"><a class="nav-link" href="#">Home</a></nav>', 'desc_fa' => 'منوی ناوبری', 'desc_en' => 'Navigation menu'],
            ['code' => '<section id="hero"><h2>Welcome</h2><p>Description</p></section>', 'desc_fa' => 'بخش هیرو صفحه', 'desc_en' => 'Hero section'],
            ['code' => '<form><input type="email" placeholder="Email" required></form>', 'desc_fa' => 'فیلد ایمیل با اعتبارسنجی', 'desc_en' => 'Email field with validation']
        ]
    ],
    'css' => [
        'easy' => [
            ['code' => 'color: blue;', 'desc_fa' => 'تغییر رنگ متن', 'desc_en' => 'Change text color'],
            ['code' => 'font-size: 16px;', 'desc_fa' => 'تنظیم اندازه فونت', 'desc_en' => 'Set font size'],
            ['code' => 'margin: 20px;', 'desc_fa' => 'فاصله بیرونی', 'desc_en' => 'Outer spacing'],
            ['code' => 'padding: 10px;', 'desc_fa' => 'فاصله درونی', 'desc_en' => 'Inner spacing'],
            ['code' => 'display: flex;', 'desc_fa' => 'چینش فلکس‌باکس', 'desc_en' => 'Flexbox layout']
        ],
        'medium' => [
            ['code' => 'background: linear-gradient(135deg, #667eea, #764ba2);', 'desc_fa' => 'گرادیانت رنگی', 'desc_en' => 'Color gradient'],
            ['code' => 'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);', 'desc_fa' => 'سایه‌دار کردن المان', 'desc_en' => 'Add shadow to element'],
            ['code' => 'transform: translateY(-5px);', 'desc_fa' => 'جابجایی عمودی', 'desc_en' => 'Vertical movement'],
            ['code' => 'transition: all 0.3s ease;', 'desc_fa' => 'انیمیشن نرم', 'desc_en' => 'Smooth animation'],
            ['code' => 'border-radius: 10px;', 'desc_fa' => 'گوشه‌های گرد', 'desc_en' => 'Rounded corners']
        ],
        'hard' => [
            ['code' => '@media (max-width: 768px) { .container { width: 100%; } }', 'desc_fa' => 'طراحی ریسپانسیو موبایل', 'desc_en' => 'Responsive mobile design'],
            ['code' => '.btn:hover { transform: scale(1.1); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }', 'desc_fa' => 'افکت هاور دکمه', 'desc_en' => 'Button hover effect'],
            ['code' => 'animation: slideIn 0.5s ease-in-out forwards;', 'desc_fa' => 'انیمیشن لغزشی', 'desc_en' => 'Slide animation'],
            ['code' => 'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));', 'desc_fa' => 'گرید خودکار', 'desc_en' => 'Auto grid layout']
        ]
    ],
    'javascript' => [
        'easy' => [
            ['code' => 'console.log("Hello World");', 'desc_fa' => 'چاپ در کنسول', 'desc_en' => 'Print to console'],
            ['code' => 'const name = "John";', 'desc_fa' => 'تعریف ثابت', 'desc_en' => 'Define constant'],
            ['code' => 'let age = 25;', 'desc_fa' => 'تعریف متغیر', 'desc_en' => 'Define variable'],
            ['code' => 'alert("Welcome!");', 'desc_fa' => 'نمایش پیغام', 'desc_en' => 'Show alert'],
            ['code' => 'document.getElementById("demo");', 'desc_fa' => 'انتخاب المان', 'desc_en' => 'Select element']
        ],
        'medium' => [
            ['code' => 'function calculateSum(a, b) { return a + b; }', 'desc_fa' => 'تابع جمع دو عدد', 'desc_en' => 'Sum two numbers'],
            ['code' => 'const numbers = [1, 2, 3, 4, 5];', 'desc_fa' => 'آرایه اعداد', 'desc_en' => 'Array of numbers'],
            ['code' => 'numbers.forEach(num => console.log(num));', 'desc_fa' => 'حلقه روی آرایه', 'desc_en' => 'Loop through array'],
            ['code' => 'const user = { name: "Alice", age: 30 };', 'desc_fa' => 'شیء کاربر', 'desc_en' => 'User object'],
            ['code' => 'if (score > 50) { result = "Pass"; }', 'desc_fa' => 'شرط قبولی', 'desc_en' => 'Pass condition']
        ],
        'hard' => [
            ['code' => 'fetch("/api/data").then(res => res.json()).then(data => console.log(data));', 'desc_fa' => 'دریافت داده از API', 'desc_en' => 'Fetch data from API'],
            ['code' => 'const fibonacci = n => n <= 1 ? n : fibonacci(n-1) + fibonacci(n-2);', 'desc_fa' => 'تابع بازگشتی فیبوناچی', 'desc_en' => 'Recursive fibonacci'],
            ['code' => 'const debounce = (fn, delay) => { let timer; return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); }; };', 'desc_fa' => 'تاخیر اجرای تابع', 'desc_en' => 'Delay function execution'],
            ['code' => 'Promise.all([fetchUser(), fetchPosts()]).then(([user, posts]) => render(user, posts));', 'desc_fa' => 'اجرای موازی Promise', 'desc_en' => 'Parallel promises']
        ]
    ],
    'php' => [
        'easy' => [
            ['code' => 'echo "Hello World";', 'desc_fa' => 'چاپ متن', 'desc_en' => 'Print text'],
            ['code' => '$name = "John";', 'desc_fa' => 'تعریف متغیر', 'desc_en' => 'Define variable'],
            ['code' => '$age = 25;', 'desc_fa' => 'متغیر عددی', 'desc_en' => 'Number variable'],
            ['code' => 'if ($x > 10) { }', 'desc_fa' => 'شرط ساده', 'desc_en' => 'Simple condition'],
            ['code' => 'for ($i = 0; $i < 5; $i++) { }', 'desc_fa' => 'حلقه شمارشی', 'desc_en' => 'For loop']
        ],
        'medium' => [
            ['code' => 'function getName() { return "John"; }', 'desc_fa' => 'تابع برگشت نام', 'desc_en' => 'Return name function'],
            ['code' => '$users = ["Alice", "Bob", "Charlie"];', 'desc_fa' => 'آرایه کاربران', 'desc_en' => 'Users array'],
            ['code' => 'foreach ($users as $user) { echo $user; }', 'desc_fa' => 'حلقه روی آرایه', 'desc_en' => 'Loop array'],
            ['code' => '$result = $pdo->query("SELECT * FROM users");', 'desc_fa' => 'کوئری دیتابیس', 'desc_en' => 'Database query'],
            ['code' => 'class User { public $name; public $email; }', 'desc_fa' => 'کلاس کاربر', 'desc_en' => 'User class']
        ],
        'hard' => [
            ['code' => '$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");', 'desc_fa' => 'کوئری آماده امن', 'desc_en' => 'Prepared statement'],
            ['code' => 'try { $result = processData(); } catch (Exception $e) { log($e); }', 'desc_fa' => 'مدیریت خطا', 'desc_en' => 'Error handling'],
            ['code' => 'namespace App\\Controllers; use App\\Models\\User;', 'desc_fa' => 'فضای نام و import', 'desc_en' => 'Namespace and import'],
            ['code' => 'public function index(Request $request): Response { return view("home"); }', 'desc_fa' => 'متد کنترلر', 'desc_en' => 'Controller method']
        ]
    ],
    'python' => [
        'easy' => [
            ['code' => 'print("Hello World")', 'desc_fa' => 'چاپ متن', 'desc_en' => 'Print text'],
            ['code' => 'name = "John"', 'desc_fa' => 'تعریف متغیر', 'desc_en' => 'Define variable'],
            ['code' => 'age = 25', 'desc_fa' => 'متغیر عددی', 'desc_en' => 'Number variable'],
            ['code' => 'if x > 10:', 'desc_fa' => 'شرط ساده', 'desc_en' => 'Simple condition'],
            ['code' => 'for i in range(5):', 'desc_fa' => 'حلقه تکرار', 'desc_en' => 'For loop']
        ],
        'medium' => [
            ['code' => 'def calculate_sum(a, b): return a + b', 'desc_fa' => 'تابع جمع', 'desc_en' => 'Sum function'],
            ['code' => 'numbers = [1, 2, 3, 4, 5]', 'desc_fa' => 'لیست اعداد', 'desc_en' => 'Numbers list'],
            ['code' => 'result = [x * 2 for x in numbers]', 'desc_fa' => 'List Comprehension', 'desc_en' => 'List comprehension'],
            ['code' => 'with open("file.txt", "r") as f: data = f.read()', 'desc_fa' => 'خواندن فایل', 'desc_en' => 'Read file'],
            ['code' => 'class User: def __init__(self, name): self.name = name', 'desc_fa' => 'کلاس کاربر', 'desc_en' => 'User class']
        ],
        'hard' => [
            ['code' => 'def fibonacci(n): return n if n <= 1 else fibonacci(n-1) + fibonacci(n-2)', 'desc_fa' => 'فیبوناچی بازگشتی', 'desc_en' => 'Recursive fibonacci'],
            ['code' => '@decorator def my_function(): pass', 'desc_fa' => 'دکوراتور تابع', 'desc_en' => 'Function decorator'],
            ['code' => 'lambda x, y: x + y if x > 0 else y', 'desc_fa' => 'تابع لامبدا', 'desc_en' => 'Lambda function'],
            ['code' => 'import asyncio; async def fetch_data(): return await request()', 'desc_fa' => 'تابع async', 'desc_en' => 'Async function']
        ]
    ],
    'java' => [
        'easy' => [
            ['code' => 'System.out.println("Hello");', 'desc_fa' => 'چاپ در کنسول', 'desc_en' => 'Print to console'],
            ['code' => 'String name = "John";', 'desc_fa' => 'متغیر رشته‌ای', 'desc_en' => 'String variable'],
            ['code' => 'int age = 25;', 'desc_fa' => 'متغیر عددی', 'desc_en' => 'Integer variable'],
            ['code' => 'if (x > 10) { }', 'desc_fa' => 'شرط ساده', 'desc_en' => 'Simple condition'],
            ['code' => 'for (int i = 0; i < 5; i++) { }', 'desc_fa' => 'حلقه for', 'desc_en' => 'For loop']
        ],
        'medium' => [
            ['code' => 'public static void main(String[] args) { }', 'desc_fa' => 'متد اصلی برنامه', 'desc_en' => 'Main method'],
            ['code' => 'ArrayList<String> list = new ArrayList<>();', 'desc_fa' => 'لیست پویا', 'desc_en' => 'Dynamic list'],
            ['code' => 'public class User { private String name; }', 'desc_fa' => 'کلاس با فیلد خصوصی', 'desc_en' => 'Class with private field'],
            ['code' => 'try { processData(); } catch (Exception e) { }', 'desc_fa' => 'مدیریت استثنا', 'desc_en' => 'Exception handling'],
            ['code' => 'public int calculate(int a, int b) { return a + b; }', 'desc_fa' => 'متد محاسبه', 'desc_en' => 'Calculate method']
        ],
        'hard' => [
            ['code' => 'public <T> List<T> filter(List<T> list, Predicate<T> predicate) { }', 'desc_fa' => 'متد جنریک', 'desc_en' => 'Generic method'],
            ['code' => 'Stream.of(1, 2, 3).map(x -> x * 2).collect(Collectors.toList());', 'desc_fa' => 'Stream API', 'desc_en' => 'Stream API'],
            ['code' => '@Override public boolean equals(Object obj) { return this.id == obj.id; }', 'desc_fa' => 'Override متد', 'desc_en' => 'Override method'],
            ['code' => 'CompletableFuture.supplyAsync(() => getData()).thenApply(data -> process(data));', 'desc_fa' => 'برنامه‌نویسی Async', 'desc_en' => 'Async programming']
        ]
    ],
    'cpp' => [
        'easy' => [
            ['code' => 'cout << "Hello World";', 'desc_fa' => 'چاپ در خروجی', 'desc_en' => 'Print output'],
            ['code' => 'int x = 10;', 'desc_fa' => 'متغیر عددی', 'desc_en' => 'Integer variable'],
            ['code' => 'string name = "John";', 'desc_fa' => 'متغیر رشته‌ای', 'desc_en' => 'String variable'],
            ['code' => 'if (x > 5) { }', 'desc_fa' => 'شرط ساده', 'desc_en' => 'Simple condition'],
            ['code' => 'for (int i = 0; i < 5; i++) { }', 'desc_fa' => 'حلقه for', 'desc_en' => 'For loop']
        ],
        'medium' => [
            ['code' => 'vector<int> numbers = {1, 2, 3, 4, 5};', 'desc_fa' => 'وکتور اعداد', 'desc_en' => 'Vector of numbers'],
            ['code' => 'class User { public: string name; };', 'desc_fa' => 'کلاس کاربر', 'desc_en' => 'User class'],
            ['code' => 'void printName(string name) { cout << name; }', 'desc_fa' => 'تابع چاپ نام', 'desc_en' => 'Print name function'],
            ['code' => 'int* ptr = new int(10);', 'desc_fa' => 'اشاره‌گر و حافظه', 'desc_en' => 'Pointer and memory'],
            ['code' => 'std::sort(vec.begin(), vec.end());', 'desc_fa' => 'مرتب‌سازی وکتور', 'desc_en' => 'Sort vector']
        ],
        'hard' => [
            ['code' => 'template<typename T> T max(T a, T b) { return a > b ? a : b; }', 'desc_fa' => 'تابع قالب', 'desc_en' => 'Template function'],
            ['code' => 'auto lambda = [](int x) { return x * 2; };', 'desc_fa' => 'تابع lambda', 'desc_en' => 'Lambda function'],
            ['code' => 'unique_ptr<User> user = make_unique<User>("John");', 'desc_fa' => 'اشاره‌گر هوشمند', 'desc_en' => 'Smart pointer'],
            ['code' => 'std::thread t([](){ processData(); }); t.join();', 'desc_fa' => 'Thread و همزمانی', 'desc_en' => 'Thread and concurrency']
        ]
    ]
];

// ترجمه‌ها
$t = [
    'fa' => [
        'code_master' => 'استاد کدنویسی',
        'back' => 'بازگشت',
        'select_language' => 'انتخاب زبان برنامه‌نویسی',
        'select_level' => 'انتخاب سطح',
        'easy' => 'آسان',
        'medium' => 'متوسط',
        'hard' => 'سخت',
        'start_coding' => 'شروع کدنویسی',
        'wpm' => 'سرعت',
        'accuracy' => 'دقت',
        'score' => 'امتیاز',
        'lines_left' => 'خطوط باقی‌مانده',
        'type_code' => 'کد را تایپ کنید...',
        'game_complete' => 'تمرین تکمیل شد!',
        'coding_master' => 'استاد کدنویسی!',
        'great_job' => 'عالی بود!',
        'keep_practicing' => 'به تمرین ادامه بده',
        'total_lines' => 'کل خطوط',
        'perfect_lines' => 'خطوط کامل',
        'exit' => 'خروج',
        'retry' => 'تلاش مجدد',
        'instructions_title' => 'راهنما',
        'inst_1' => '💻 کدهای واقعی رو تایپ کن',
        'inst_2' => '⚡ هر چه سریع‌تر، امتیاز بیشتر',
        'inst_3' => '🎯 دقت مهم‌تره از سرعت',
        'inst_4' => '🏆 خط بدون خطا = بونوس',
        'new_record' => 'رکورد جدید!',
        'languages' => [
            'html' => 'HTML',
            'css' => 'CSS',
            'javascript' => 'JavaScript',
            'php' => 'PHP',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++'
        ]
    ],
    'en' => [
        'code_master' => 'Code Master',
        'back' => 'Back',
        'select_language' => 'Select Programming Language',
        'select_level' => 'Select Difficulty',
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
        'start_coding' => 'Start Coding',
        'wpm' => 'WPM',
        'accuracy' => 'Accuracy',
        'score' => 'Score',
        'lines_left' => 'Lines Left',
        'type_code' => 'Type the code...',
        'game_complete' => 'Challenge Complete!',
        'coding_master' => 'Coding Master!',
        'great_job' => 'Great Job!',
        'keep_practicing' => 'Keep Practicing',
        'total_lines' => 'Total Lines',
        'perfect_lines' => 'Perfect Lines',
        'exit' => 'Exit',
        'retry' => 'Retry',
        'instructions_title' => 'Instructions',
        'inst_1' => '💻 Type real code snippets',
        'inst_2' => '⚡ Faster = More points',
        'inst_3' => '🎯 Accuracy matters',
        'inst_4' => '🏆 Perfect line = Bonus',
        'new_record' => 'New Record!',
        'languages' => [
            'html' => 'HTML',
            'css' => 'CSS',
            'javascript' => 'JavaScript',
            'php' => 'PHP',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++'
        ]
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['code_master'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
            background: #0d1117;
            min-height: 100vh;
            overflow: hidden;
            color: white;
        }

        .game-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .game-header {
            background: rgba(13, 17, 23, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #30363d;
        }

        .game-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #58a6ff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-bar {
            display: flex;
            gap: 2rem;
        }

        .stat-box {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            color: #58a6ff;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #8b949e;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .game-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            gap: 2rem;
        }

        .code-display-container {
            background: #161b22;
            border: 2px solid #30363d;
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #30363d;
        }

        .code-lang-badge {
            padding: 6px 12px;
            background: #238636;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .code-line-number {
            color: #58a6ff;
            font-size: 0.9rem;
        }

        .code-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            color: #c9d1d9;
            min-height: 80px;
            line-height: 1.8;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .code-description {
            margin-top: 1rem;
            padding: 0.8rem;
            background: rgba(88, 166, 255, 0.1);
            border-radius: 8px;
            border-left: 3px solid #58a6ff;
            color: #8b949e;
            font-size: 0.9rem;
            text-align: right;
        }

        .typing-input {
            width: 90%;
            max-width: 900px;
            padding: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            background: #0d1117;
            color: #c9d1d9;
            border: 2px solid #30363d;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .typing-input:focus {
            outline: none;
            border-color: #58a6ff;
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.2);
        }

        .typing-input.correct {
            border-color: #238636;
        }

        .typing-input.error {
            border-color: #f85149;
            animation: shake 0.3s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .progress-container {
            width: 90%;
            max-width: 900px;
        }

        .progress-bar {
            height: 8px;
            background: #161b22;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #58a6ff, #238636);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            color: #8b949e;
            font-size: 0.9rem;
            text-align: center;
        }

        .start-screen,
        .setup-screen,
        .gameover-screen {
            position: absolute;
            inset: 0;
            background: rgba(13, 17, 23, 0.98);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .screen-content {
            background: #161b22;
            padding: 1.5rem 2.5rem;
            border-radius: 16px;
            text-align: center;
            max-width: 850px;
            border: 2px solid #30363d;
        }

        .screen-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            animation: bounce 1s ease infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .screen-title {
            font-size: 1.7rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            color: #58a6ff;
        }

        .language-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.8rem;
            margin: 1rem 0;
        }

        .lang-option {
            padding: 0.8rem 0.6rem;
            background: #0d1117;
            border: 2px solid #30363d;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .lang-option:hover {
            border-color: #58a6ff;
            transform: translateY(-3px);
        }

        .lang-option.selected {
            border-color: #238636;
            background: rgba(35, 134, 54, 0.1);
        }

        .lang-icon {
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
        }

        .lang-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #c9d1d9;
        }

        .level-selector {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
            margin: 1rem 0;
        }

        .level-btn {
            padding: 0.8rem 1.5rem;
            background: #0d1117;
            border: 2px solid #30363d;
            border-radius: 12px;
            color: #c9d1d9;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .level-btn:hover {
            border-color: #58a6ff;
        }

        .level-btn.selected {
            border-color: #238636;
            background: rgba(35, 134, 54, 0.1);
        }

        .start-btn {
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #58a6ff, #238636);
            border: none;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.8rem;
        }

        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(88, 166, 255, 0.4);
        }

        .start-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .result-stat {
            padding: 1.5rem;
            background: #0d1117;
            border-radius: 12px;
            border: 2px solid #30363d;
        }

        .result-stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #58a6ff;
        }

        .result-stat-label {
            color: #8b949e;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1.25rem;
            border: 2px solid #30363d;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #0d1117;
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #58a6ff, #238636);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .control-btn {
            width: 50px;
            height: 50px;
            background: #0d1117;
            border: 2px solid #30363d;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            border-color: #f85149;
        }

        .instructions {
            text-align: right;
            margin: 1rem 0;
            padding: 0.8rem;
            background: rgba(88, 166, 255, 0.1);
            border-radius: 12px;
            border: 2px solid rgba(88, 166, 255, 0.3);
        }

        .instructions li {
            padding: 4px 0;
            color: #8b949e;
            line-height: 1.4;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <div class="game-container">
        <!-- Header -->
        <div class="game-header">
            <h1 class="game-title">
                <span>💻</span>
                <span><?= $tr['code_master'] ?></span>
            </h1>

            <div class="stats-bar">
                <div class="stat-box">
                    <div class="stat-value" id="wpmDisplay">0</div>
                    <div class="stat-label"><?= $tr['wpm'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="accuracyDisplay">100%</div>
                    <div class="stat-label"><?= $tr['accuracy'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="scoreDisplay">0</div>
                    <div class="stat-label"><?= $tr['score'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="linesLeftDisplay">10</div>
                    <div class="stat-label"><?= $tr['lines_left'] ?></div>
                </div>
            </div>

            <a href="games.php" class="control-btn"></a>
        </div>

        <!-- Game Content -->
        <div class="game-content">
            <div class="code-display-container">
                <div class="code-header">
                    <span class="code-lang-badge" id="codeLangBadge">JavaScript</span>
                    <span class="code-line-number" id="lineNumber">Line 1/10</span>
                </div>
                <pre class="code-display" id="codeDisplay">console.log("Ready to code!");</pre>
                <div class="code-description" id="codeDescription">📝 توضیح کد در اینجا نمایش داده می‌شود</div>
            </div>

            <input type="text" class="typing-input" id="typingInput" placeholder="<?= $tr['type_code'] ?>"
                autocomplete="off" spellcheck="false" disabled>

            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar"></div>
                </div>
                <div class="progress-text" id="progressText">0 / 10</div>
            </div>
        </div>

        <!-- Setup Screen -->
        <div class="setup-screen" id="setupScreen">
            <div class="screen-content">
                <div class="screen-icon">💻</div>
                <h2 class="screen-title"><?= $tr['code_master'] ?></h2>

                <h3 style="color: #58a6ff; margin: 1rem 0 0.6rem; font-size: 1.1rem;"><?= $tr['select_language'] ?></h3>
                <div class="language-selector" id="languageSelector">
                    <div class="lang-option" data-lang="javascript">
                        <div class="lang-icon">JS</div>
                        <div class="lang-name">JavaScript</div>
                    </div>
                    <div class="lang-option" data-lang="html">
                        <div class="lang-icon">HTML</div>
                        <div class="lang-name">HTML</div>
                    </div>
                    <div class="lang-option" data-lang="css">
                        <div class="lang-icon">CSS</div>
                        <div class="lang-name">CSS</div>
                    </div>
                    <div class="lang-option" data-lang="php">
                        <div class="lang-icon">PHP</div>
                        <div class="lang-name">PHP</div>
                    </div>
                    <div class="lang-option" data-lang="python">
                        <div class="lang-icon">PY</div>
                        <div class="lang-name">Python</div>
                    </div>
                    <div class="lang-option" data-lang="java">
                        <div class="lang-icon">☕</div>
                        <div class="lang-name">Java</div>
                    </div>
                    <div class="lang-option" data-lang="cpp">
                        <div class="lang-icon">C++</div>
                        <div class="lang-name">C++</div>
                    </div>
                </div>

                <h3 style="color: #58a6ff; margin: 1rem 0 0.6rem; font-size: 1.1rem;"><?= $tr['select_level'] ?></h3>
                <div class="level-selector">
                    <button class="level-btn selected" data-level="easy"><?= $tr['easy'] ?></button>
                    <button class="level-btn" data-level="medium"><?= $tr['medium'] ?></button>
                    <button class="level-btn" data-level="hard"><?= $tr['hard'] ?></button>
                </div>

                <ul class="instructions">
                    <li><?= $tr['inst_1'] ?></li>
                    <li><?= $tr['inst_2'] ?></li>
                    <li><?= $tr['inst_3'] ?></li>
                    <li><?= $tr['inst_4'] ?></li>
                </ul>

                <button class="start-btn" id="startBtn" disabled>
                    🚀 <?= $tr['start_coding'] ?>
                </button>
            </div>
        </div>

        <!-- Game Over Screen -->
        <div class="gameover-screen" id="gameoverScreen" style="display: none;">
            <div class="screen-content">
                <div class="screen-icon" id="resultIcon">🏆</div>
                <h2 class="screen-title" id="resultTitle"><?= $tr['game_complete'] ?></h2>

                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalWPM">0</div>
                        <div class="result-stat-label"><?= $tr['wpm'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalAccuracy">0%</div>
                        <div class="result-stat-label"><?= $tr['accuracy'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalScore">0</div>
                        <div class="result-stat-label"><?= $tr['score'] ?></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <a href="games.php" class="btn btn-secondary">
                        🏠 <?= $tr['exit'] ?>
                    </a>
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        🔄 <?= $tr['retry'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.GAME_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            userId: <?= $user['id'] ?>,
            language: '<?= $currentLang ?>',
            codeSamples: <?= json_encode($codeSamples, JSON_UNESCAPED_UNICODE) ?>,
            translations: <?= json_encode($tr, JSON_UNESCAPED_UNICODE) ?>,
            bestWPM: <?= $bestStats['best_wpm'] ?? 0 ?>,
            bestAccuracy: <?= $bestStats['best_accuracy'] ?? 0 ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/game-code-master.js"></script>
</body>

</html>