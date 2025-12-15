<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get forum categories
$categories = [
    [
        'id' => 1,
        'name' => 'Career Advice',
        'description' => 'Get advice on career development, job hunting, and professional growth',
        'icon' => 'fa-bullseye',
        'topics' => 125,
        'posts' => 1200
    ],
    [
        'id' => 2,
        'name' => 'Technical Skills',
        'description' => 'Discuss programming, design, data science, and other technical skills',
        'icon' => 'fa-code',
        'topics' => 89,
        'posts' => 850
    ],
    [
        'id' => 3,
        'name' => 'Interview Preparation',
        'description' => 'Share interview experiences, tips, and practice questions',
        'icon' => 'fa-user-tie',
        'topics' => 45,
        'posts' => 420
    ],
    [
        'id' => 4,
        'name' => 'Resume & Portfolio',
        'description' => 'Get feedback on resumes, portfolios, and personal branding',
        'icon' => 'fa-file-alt',
        'topics' => 67,
        'posts' => 580
    ],
    [
        'id' => 5,
        'name' => 'Industry Insights',
        'description' => 'Discuss industry trends, company insights, and market news',
        'icon' => 'fa-chart-line',
        'topics' => 32,
        'posts' => 310
    ],
    [
        'id' => 6,
        'name' => 'Networking',
        'description' => 'Connect with professionals, mentors, and peers',
        'icon' => 'fa-users',
        'topics' => 56,
        'posts' => 490
    ]
];

// Get recent topics
$recent_topics = [
    [
        'id' => 1,
        'title' => 'How to transition from web development to data science?',
        'category' => 'Career Advice',
        'author' => 'John Doe',
        'replies' => 24,
        'views' => 156,
        'last_activity' => '2 hours ago'
    ],
    [
        'id' => 2,
        'title' => 'PHP vs Python for backend development in 2024',
        'category' => 'Technical Skills',
        'author' => 'Sarah Johnson',
        'replies' => 42,
        'views' => 289,
        'last_activity' => '5 hours ago'
    ],
    [
        'id' => 3,
        'title' => 'FAANG interview preparation guide - My experience',
        'category' => 'Interview Preparation',
        'author' => 'Mike Chen',
        'replies' => 18,
        'views' => 210,
        'last_activity' => '1 day ago'
    ],
    [
        'id' => 4,
        'title' => 'Resume review for senior developer position',
        'category' => 'Resume & Portfolio',
        'author' => 'Alex Wilson',
        'replies' => 12,
        'views' => 134,
        'last_activity' => '2 days ago'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Forums - CareerPath</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-comments"></i> Discussion Forums</h1>
                <p>Connect, learn, and share with professionals</p>
            </div>
            
            <div class="forum-search">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search discussions, topics, or users...">
                </div>
                <button class="btn btn-primary" id="newTopicBtn">
                    <i class="fas fa-plus"></i> New Topic
                </button>
            </div>
            
            <div class="forum-welcome">
                <div class="welcome-content">
                    <h2>Welcome to CareerPath Forums!</h2>
                    <p>Join discussions, ask questions, and share your knowledge with our community of professionals.</p>
                    <div class="forum-stats">
                        <div class="stat">
                            <strong><?php echo array_sum(array_column($categories, 'topics')); ?></strong>
                            <span>Topics</span>
                        </div>
                        <div class="stat">
                            <strong><?php echo array_sum(array_column($categories, 'posts')); ?></strong>
                            <span>Posts</span>
                        </div>
                        <div class="stat">
                            <strong><?php echo count($categories); ?></strong>
                            <span>Categories</span>
                        </div>
                        <div class="stat">
                            <strong>1,245</strong>
                            <span>Members</span>
                        </div>
                    </div>
                </div>
                <div class="welcome-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-book"></i> Forum Rules
                    </button>
                    <button class="btn btn-outline">
                        <i class="fas fa-star"></i> Featured Topics
                    </button>
                </div>
            </div>
            
            <div class="forum-categories">
                <h2 class="section-title">Categories</h2>
                <div class="categories-grid">
                    <?php foreach($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas <?php echo $category['icon']; ?>"></i>
                        </div>
                        <div class="category-info">
                            <h3><?php echo $category['name']; ?></h3>
                            <p><?php echo $category['description']; ?></p>
                            <div class="category-stats">
                                <span><?php echo $category['topics']; ?> Topics</span>
                                <span><?php echo $category['posts']; ?> Posts</span>
                            </div>
                        </div>
                        <a href="forum_category.php?id=<?php echo $category['id']; ?>" class="btn btn-outline">
                            Browse <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="recent-topics">
                <div class="section-header">
                    <h2 class="section-title">Recent Topics</h2>
                    <a href="forum_topics.php" class="view-all">View All Topics <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="topics-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Category</th>
                                <th>Replies</th>
                                <th>Views</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_topics as $topic): ?>
                            <tr>
                                <td>
                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="topic-title">
                                        <?php echo htmlspecialchars($topic['title']); ?>
                                    </a>
                                    <small>by <?php echo $topic['author']; ?></small>
                                </td>
                                <td>
                                    <span class="topic-category"><?php echo $topic['category']; ?></span>
                                </td>
                                <td>
                                    <span class="topic-replies"><?php echo $topic['replies']; ?></span>
                                </td>
                                <td>
                                    <span class="topic-views"><?php echo $topic['views']; ?></span>
                                </td>
                                <td>
                                    <span class="topic-activity"><?php echo $topic['last_activity']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="popular-tags">
                <h2 class="section-title">Popular Tags</h2>
                <div class="tags-cloud">
                    <a href="#" class="tag">#careeradvice</a>
                    <a href="#" class="tag">#interviewtips</a>
                    <a href="#" class="tag">#resume</a>
                    <a href="#" class="tag">#php</a>
                    <a href="#" class="tag">#python</a>
                    <a href="#" class="tag">#javascript</a>
                    <a href="#" class="tag">#datascience</a>
                    <a href="#" class="tag">#webdevelopment</a>
                    <a href="#" class="tag">#remotework</a>
                    <a href="#" class="tag">#salarynegotiation</a>
                    <a href="#" class="tag">#mentorship</a>
                    <a href="#" class="tag">#networking</a>
                </div>
            </div>
            
            <div class="forum-guide">
                <div class="guide-card">
                    <div class="guide-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="guide-content">
                        <h3>New to the forums?</h3>
                        <p>Check out our getting started guide and community guidelines</p>
                        <a href="#" class="btn btn-outline">Learn More</a>
                    </div>
                </div>
                
                <div class="guide-card">
                    <div class="guide-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="guide-content">
                        <h3>Top Contributors</h3>
                        <p>Recognize our most helpful community members</p>
                        <a href="#" class="btn btn-outline">View Leaders</a>
                    </div>
                </div>
                
                <div class="guide-card">
                    <div class="guide-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="guide-content">
                        <h3>Upcoming Events</h3>
                        <p>Join our live Q&A sessions and webinars</p>
                        <a href="#" class="btn btn-outline">View Events</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- New Topic Modal -->
    <div id="newTopicModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Topic</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form class="modal-form">
                <div class="form-group">
                    <label for="topicTitle">Topic Title *</label>
                    <input type="text" id="topicTitle" required placeholder="Enter a descriptive title for your topic">
                </div>
                
                <div class="form-group">
                    <label for="topicCategory">Category *</label>
                    <select id="topicCategory" required>
                        <option value="">Select a category</option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="topicContent">Content *</label>
                    <textarea id="topicContent" rows="6" required placeholder="Write your post here... You can use markdown formatting"></textarea>
                    <div class="editor-tools">
                        <button type="button" class="tool-btn" data-tool="bold"><i class="fas fa-bold"></i></button>
                        <button type="button" class="tool-btn" data-tool="italic"><i class="fas fa-italic"></i></button>
                        <button type="button" class="tool-btn" data-tool="code"><i class="fas fa-code"></i></button>
                        <button type="button" class="tool-btn" data-tool="link"><i class="fas fa-link"></i></button>
                        <button type="button" class="tool-btn" data-tool="image"><i class="fas fa-image"></i></button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="topicTags">Tags</label>
                    <input type="text" id="topicTags" placeholder="Add tags separated by commas (e.g., php, career, interview)">
                    <small>Add up to 5 tags to help others find your topic</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post Topic</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/forum.js"></script>
</body>
</html>