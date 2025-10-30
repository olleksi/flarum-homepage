<?php

namespace YourUsername\Homepage\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\Tag;
use Flarum\Discussion\Discussion;
use Flarum\User\User;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomepageController implements RequestHandlerInterface
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Отримуємо дані
        $forumTitle = $this->settings->get('forum_title', 'Форум');
        $tags = Tag::whereNull('parent_id')->orderBy('position')->limit(6)->get();
        $discussions = Discussion::latest()->limit(5)->get();
        $usersCount = User::count();
        $discussionsCount = Discussion::count();
        $postsCount = \DB::table('posts')->count();

        $html = '<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ласкаво просимо - ' . htmlspecialchars($forumTitle) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .hero {
            background: white;
            border-radius: 15px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .hero h1 {
            font-size: 3em;
            color: #333;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 1.3em;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1.1em;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .category {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .category:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .category h3 {
            color: #333;
            margin-bottom: 8px;
            font-size: 1.3em;
        }
        .category p {
            color: #666;
            font-size: 0.95em;
        }
        .category-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            color: white;
            font-size: 1.2em;
            margin-right: 15px;
            float: left;
        }
        .category-stats {
            margin-top: 10px;
            font-size: 0.85em;
            color: #888;
        }
        .post-item {
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            transition: background 0.3s;
            cursor: pointer;
        }
        .post-item:hover {
            background: #f0f0f0;
        }
        .post-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 1.1em;
        }
        .post-meta {
            color: #999;
            font-size: 0.85em;
        }
        .post-author {
            color: #667eea;
            font-weight: bold;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
            margin: 10px;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>🎉 Ласкаво просимо на ' . htmlspecialchars($forumTitle) . '!</h1>
            <p>Приєднуйтесь до нашої спільноти, діліться досвідом, знаходьте відповіді на свої запитання та спілкуйтесь з однодумцями!</p>
            <a href="/" class="btn">Перейти до форуму</a>
            <a href="/signup" class="btn btn-secondary">Зареєструватися</a>
        </div>

        <div class="content-grid">
            <div class="card">
                <h2>📚 Розділи форуму</h2>';
        
        $icons = ['💬', '❓', '💡', '📰', '🎮', '🛠️'];
        $iconIndex = 0;
        
        foreach ($tags as $tag) {
            $icon = $icons[$iconIndex % count($icons)];
            $iconIndex++;
            
            $html .= '
                <div class="category" onclick="window.location.href=\'/t/' . htmlspecialchars($tag->slug) . '\'">
                    <span class="category-icon">' . $icon . '</span>
                    <h3>' . htmlspecialchars($tag->name) . '</h3>
                    <p>' . htmlspecialchars($tag->description ?: 'Обговорення на різні теми') . '</p>
                    <div class="category-stats">
                        📝 ' . $tag->discussion_count . ' ' . $this->pluralize($tag->discussion_count, 'тема', 'теми', 'тем') . ' • 
                        💬 ' . $tag->post_count . ' ' . $this->pluralize($tag->post_count, 'повідомлення', 'повідомлення', 'повідомлень') . '
                    </div>
                </div>';
        }
        
        $html .= '
            </div>

            <div class="card">
                <h2>🔥 Останні повідомлення</h2>';
        
        foreach ($discussions as $discussion) {
            $author = $discussion->user;
            $timeAgo = $this->timeAgo($discussion->created_at);
            
            $html .= '
                <div class="post-item" onclick="window.location.href=\'/d/' . $discussion->id . '\'">
                    <div class="post-title">' . htmlspecialchars($discussion->title) . '</div>
                    <div class="post-meta">
                        <span class="post-author">' . htmlspecialchars($author ? $author->display_name : 'Користувач') . '</span> • ' . $timeAgo . '
                    </div>
                </div>';
        }
        
        $html .= '
                <a href="/" class="btn" style="width: 100%; text-align: center; margin-top: 15px;">Всі обговорення →</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">' . number_format($usersCount) . '</div>
                <div class="stat-label">Користувачів</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">' . number_format($postsCount) . '</div>
                <div class="stat-label">Повідомлень</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">' . number_format($discussionsCount) . '</div>
                <div class="stat-label">Тем</div>
            </div>
        </div>

        <footer>
            <p>&copy; ' . date('Y') . ' ' . htmlspecialchars($forumTitle) . '. Всі права захищені.</p>
        </footer>
    </div>
</body>
</html>';

        return new HtmlResponse($html);
    }

    private function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'щойно';
        if ($diff < 3600) return floor($diff / 60) . ' хв тому';
        if ($diff < 86400) return floor($diff / 3600) . ' год тому';
        if ($diff < 604800) return floor($diff / 86400) . ' дн тому';
        return date('d.m.Y', $time);
    }

    private function pluralize($number, $one, $few, $many)
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100;
        
        if ($mod10 == 1 && $mod100 != 11) return $one;
        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) return $few;
        return $many;
    }
}
