-- ====================================
-- Typing Master - Lessons Data
-- داده‌های درس‌های فارسی و انگلیسی
-- ====================================

USE typing_master_db;

-- ====================================
-- درس‌های فارسی
-- ====================================

-- درس 1: ردیف وسط (مبتدی)
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('آشنایی با ردیف وسط', 'Middle Row Introduction', 'یادگیری کلیدهای اصلی صفحه‌کلید فارسی', 'Learning the home row keys in Persian', 'beginner', 'fa', 'ال لا ال لا لال لال الا الا لال الا ال لا', 1, 100, 1, 1);

-- درس 2: حروف ساده
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('تمرین حروف پایه', 'Basic Letters Practice', 'تمرین حروف پرکاربرد فارسی', 'Practice common Persian letters', 'beginner', 'fa', 'با ما نان دان تا نا با ما با تا نان دان با ما تا نا', 1, 100, 2, 1);

-- درس 3: کلمات ساده
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('کلمات ساده', 'Simple Words', 'تایپ کلمات ساده و پرکاربرد', 'Type simple and common words', 'beginner', 'fa', 'با من است این که از را به تا بر کرد گفت شد آن هم', 1, 150, 3, 1);

-- درس 4: جملات کوتاه
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('جملات کوتاه', 'Short Sentences', 'تمرین جملات کوتاه فارسی', 'Practice short Persian sentences', 'beginner', 'fa', 'من خوب هستم. او رفت. ما آمدیم. این کتاب است. آن خانه بود.', 2, 150, 4, 1);

-- درس 5: اعداد فارسی
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('اعداد فارسی', 'Persian Numbers', 'یادگیری تایپ اعداد فارسی', 'Learning to type Persian numbers', 'beginner', 'fa', '۱۲۳ ۴۵۶ ۷۸۹ ۰۱۲ ۳۴۵ ۶۷۸ ۹۰۱ ۲۳۴ ۵۶۷ ۸۹۰', 2, 150, 5, 1);

-- درس 6: ترکیب حروف
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('ترکیب حروف', 'Letter Combinations', 'تمرین ترکیبات مختلف حروف', 'Practice different letter combinations', 'intermediate', 'fa', 'سلام درود صبح شب روز ماه سال کار خانه مدرسه دانشگاه', 3, 200, 6, 1);

-- درس 7: جملات متوسط
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('جملات متوسط', 'Medium Sentences', 'تایپ جملات با طول متوسط', 'Type medium length sentences', 'intermediate', 'fa', 'امروز هوا بسیار خوب است. من به مدرسه می‌روم. او کتاب می‌خواند. ما با هم غذا خوردیم.', 3, 200, 7, 1);

-- درس 8: متن ادبی
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن ادبی', 'Literary Text', 'تایپ متن ادبی فارسی', 'Type Persian literary text', 'intermediate', 'fa', 'گر چه بهشت است نهایت آرزوی دل، لیک گلستان ز بهشت کمتر نیست. بهار آمد و خرم شد جهان، بوی گل آمد و شد بلبل خوشخوان.', 4, 250, 8, 1);

-- درس 9: شعر فارسی
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('شعر فارسی', 'Persian Poetry', 'تایپ ابیات شعر فارسی', 'Type Persian poetry verses', 'intermediate', 'fa', 'به نام خداوند جان و خرد، کزین برتر اندیشه بر نگذرد. خداوند نام و خداوند جای، خداوند روزی ده رهنمای.', 4, 250, 9, 1);

-- درس 10: نقل قول
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('نقل قول‌ها', 'Quotations', 'تایپ جملات نقل قول', 'Type quotation sentences', 'intermediate', 'fa', 'او گفت: "من فردا می‌آیم." پدرم پرسید: "کجا بودی؟" مادرم گفت: "غذا آماده است."', 5, 250, 10, 1);

-- درس 11: متن طولانی
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن طولانی', 'Long Text', 'تمرین متن‌های طولانی‌تر', 'Practice longer texts', 'advanced', 'fa', 'در دنیای امروز، فناوری اطلاعات نقش بسیار مهمی در زندگی روزمره ما ایفا می‌کند. کامپیوتر و اینترنت به ابزارهای ضروری تبدیل شده‌اند. ما می‌توانیم با استفاده از این ابزارها با دوستان خود ارتباط برقرار کنیم، اطلاعات جدید کسب کنیم و کارهای روزانه خود را انجام دهیم.', 6, 300, 11, 1);

-- درس 12: متن تخصصی
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن تخصصی', 'Technical Text', 'تایپ متن‌های تخصصی', 'Type technical texts', 'advanced', 'fa', 'برنامه‌نویسی یکی از مهارت‌های مهم در عصر دیجیتال است. زبان‌های برنامه‌نویسی مختلفی مانند پایتون، جاوا، و جاوااسکریپت وجود دارند. هر زبان ویژگی‌ها و کاربردهای خاص خود را دارد.', 7, 300, 12, 1);

-- درس 13: اصطلاحات
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('اصطلاحات رایج', 'Common Idioms', 'تمرین اصطلاحات و ضرب‌المثل‌ها', 'Practice idioms and proverbs', 'advanced', 'fa', 'آب از آب تکان نخوردن. دست و پا زدن. چشم و هم چشمی. سر به زیر بودن. دست روی دست گذاشتن.', 7, 300, 13, 1);

-- درس 14: متن داستانی
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن داستانی', 'Story Text', 'تایپ متن داستانی', 'Type story text', 'advanced', 'fa', 'روزی روزگاری در دهکده‌ای کوچک، پسر جوانی زندگی می‌کرد. او همیشه آرزو داشت که دنیا را ببیند. یک روز تصمیم گرفت که از خانه بیرون برود و به سفر برود. او کوله‌پشتی خود را برداشت و راهی شد.', 8, 350, 14, 1);

-- درس 15: متن پیچیده
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن پیچیده', 'Complex Text', 'چالش نهایی: متن پیچیده', 'Final challenge: complex text', 'advanced', 'fa', 'فرهنگ و تمدن ایران باستان یکی از غنی‌ترین و کهن‌ترین تمدن‌های جهان است. این سرزمین در طول تاریخ، شاهد ظهور و سقوط امپراتوری‌های بزرگی بوده است. هخامنشیان، اشکانیان و ساسانیان از مهم‌ترین سلسله‌های حکومتی ایران باستان به شمار می‌روند که هر کدام میراث ارزشمندی برای نسل‌های آینده به جای گذاشته‌اند.', 9, 400, 15, 1);

-- ====================================
-- درس‌های انگلیسی
-- ====================================

-- Lesson 1: Home Row (Beginner)
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('ردیف وسط انگلیسی', 'Home Row English', 'یادگیری کلیدهای اصلی', 'Learning the home row keys', 'beginner', 'en', 'asdf jkl; asdf jkl; aaa sss ddd fff jjj kkk lll ;;;', 1, 100, 16, 1);

-- Lesson 2: Basic Letters
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('حروف پایه انگلیسی', 'Basic English Letters', 'تمرین حروف پرکاربرد', 'Practice common letters', 'beginner', 'en', 'the and for are but not you all can her was one our', 1, 100, 17, 1);

-- Lesson 3: Simple Words
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('کلمات ساده', 'Simple Words', 'تایپ کلمات ساده', 'Type simple words', 'beginner', 'en', 'cat dog run jump happy sad big small hot cold good bad', 1, 150, 18, 1);

-- Lesson 4: Short Sentences
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('جملات کوتاه', 'Short Sentences', 'تمرین جملات کوتاه', 'Practice short sentences', 'beginner', 'en', 'I am here. She is happy. We are friends. He can run. They will come.', 2, 150, 19, 1);

-- Lesson 5: Numbers
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('اعداد', 'Numbers', 'یادگیری تایپ اعداد', 'Learning to type numbers', 'beginner', 'en', '123 456 789 012 345 678 901 234 567 890', 2, 150, 20, 1);

-- Lesson 6: Common Phrases
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('عبارات رایج', 'Common Phrases', 'تمرین عبارات روزمره', 'Practice daily phrases', 'intermediate', 'en', 'Good morning. How are you? Thank you very much. Nice to meet you. See you later.', 3, 200, 21, 1);

-- Lesson 7: Medium Sentences
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('جملات متوسط', 'Medium Sentences', 'تایپ جملات متوسط', 'Type medium sentences', 'intermediate', 'en', 'The quick brown fox jumps over the lazy dog. A journey of a thousand miles begins with a single step.', 3, 200, 22, 1);

-- Lesson 8: Descriptive Text
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن توصیفی', 'Descriptive Text', 'تایپ متن توصیفی', 'Type descriptive text', 'intermediate', 'en', 'The beautiful sunset painted the sky with shades of orange and pink. Birds were flying home to their nests. The cool evening breeze brought a sense of peace and tranquility.', 4, 250, 23, 1);

-- Lesson 9: Quotes
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('نقل قول‌ها', 'Famous Quotes', 'تایپ نقل قول‌های معروف', 'Type famous quotes', 'intermediate', 'en', 'To be or not to be, that is the question. I think, therefore I am. Knowledge is power. Time is money.', 4, 250, 24, 1);

-- Lesson 10: Dialogue
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('گفتگو', 'Dialogue', 'تمرین گفتگو', 'Practice dialogue', 'intermediate', 'en', 'John said, "Hello, how are you?" Mary replied, "I am fine, thank you. How about you?" He answered, "I am doing great!"', 5, 250, 25, 1);

-- Lesson 11: Long Paragraph
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('پاراگراف طولانی', 'Long Paragraph', 'تمرین متن طولانی', 'Practice long text', 'advanced', 'en', 'Technology has revolutionized the way we live, work, and communicate. In today\'s digital age, computers and the internet have become essential tools. We can connect with friends, learn new things, and accomplish daily tasks with just a few clicks. The rapid advancement of technology continues to shape our future in exciting ways.', 6, 300, 26, 1);

-- Lesson 12: Technical Text
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن فنی', 'Technical Text', 'تایپ متن فنی', 'Type technical text', 'advanced', 'en', 'Programming is an essential skill in the digital era. Various programming languages such as Python, Java, and JavaScript exist. Each language has its own unique features and applications. Understanding algorithms and data structures is fundamental to becoming a proficient programmer.', 7, 300, 27, 1);

-- Lesson 13: Business Email
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('ایمیل کاری', 'Business Email', 'تمرین نوشتن ایمیل', 'Practice writing email', 'advanced', 'en', 'Dear Sir or Madam, I am writing to inquire about the position advertised on your website. I have attached my resume for your consideration. I look forward to hearing from you. Sincerely, John Smith.', 7, 300, 28, 1);

-- Lesson 14: Story
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('داستان', 'Short Story', 'تایپ داستان کوتاه', 'Type short story', 'advanced', 'en', 'Once upon a time, in a small village, there lived a young boy who dreamed of exploring the world. One day, he decided to leave his home and embark on an adventure. He packed his backpack and set off on a journey that would change his life forever.', 8, 350, 29, 1);

-- Lesson 15: Complex Text
INSERT INTO lessons (title_fa, title_en, description_fa, description_en, level, language, content, required_level, xp_reward, order_index, is_active) VALUES
('متن پیچیده', 'Complex Text', 'چالش نهایی', 'Final challenge', 'advanced', 'en', 'The evolution of human civilization has been marked by remarkable achievements in science, art, and philosophy. Throughout history, great thinkers and innovators have pushed the boundaries of human knowledge and capability. From the ancient philosophers of Greece to the Renaissance masters, and from the Industrial Revolution to the Digital Age, humanity has continuously strived to understand the world and improve the human condition.', 9, 400, 30, 1);

-- ====================================
-- ✅ داده‌های درس‌ها با موفقیت اضافه شدند!
-- ====================================

SELECT 'Lessons data inserted successfully! ✅' AS Status;
SELECT COUNT(*) AS 'Total Lessons', language, level FROM lessons GROUP BY language, level;