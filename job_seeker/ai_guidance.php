<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$recommendations = [];

// Get user data for AI recommendations
$stmt = $conn->prepare("SELECT skills, experience, education FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Mock AI Recommendations (In production, integrate GROQ API)
function getAIRecommendations($skills, $experience, $education) {
    $recommendations = [];
    
    // Career Path Recommendations
    if(strpos(strtolower($skills), 'php') !== false || strpos(strtolower($skills), 'mysql') !== false) {
        $recommendations[] = [
            'type' => 'career_path',
            'title' => 'Full Stack Developer',
            'description' => 'Based on your PHP and MySQL skills, consider pursuing a Full Stack Developer role.',
            'steps' => ['Learn JavaScript frameworks', 'Master REST APIs', 'Study frontend technologies']
        ];
    }
    
    if(strpos(strtolower($skills), 'python') !== false) {
        $recommendations[] = [
            'type' => 'career_path',
            'title' => 'Data Scientist',
            'description' => 'Your Python knowledge is a great foundation for a career in Data Science.',
            'steps' => ['Learn Pandas & NumPy', 'Study Machine Learning basics', 'Practice data visualization']
        ];
    }
    
    // Skill Gap Analysis
    $recommendations[] = [
        'type' => 'skill_gap',
        'title' => 'Cloud Computing',
        'description' => 'Consider learning cloud platforms like AWS or Azure to enhance your marketability.',
        'resources' => ['AWS Free Tier', 'Microsoft Learn', 'Google Cloud Free Program']
    ];
    
    // Learning Resources
    $recommendations[] = [
        'type' => 'learning',
        'title' => 'Recommended Courses',
        'description' => 'Here are some courses that match your profile:',
        'courses' => [
            ['name' => 'Advanced PHP Programming', 'platform' => 'Coursera', 'duration' => '8 weeks'],
            ['name' => 'Database Design', 'platform' => 'Udemy', 'duration' => '6 weeks'],
            ['name' => 'Soft Skills Development', 'platform' => 'LinkedIn Learning', 'duration' => '4 weeks']
        ]
    ];
    
    return $recommendations;
}

if($user) {
    $recommendations = getAIRecommendations($user['skills'] ?? '', $user['experience'] ?? '', $user['education'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Career Guidance - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-robot"></i> AI Career Guidance</h1>
                <p>Personalized career recommendations powered by artificial intelligence</p>
            </div>
            
            <div class="ai-guidance-container">
                <div class="ai-sidebar">
                    <div class="ai-profile-summary">
                        <h3>Your Profile Summary</h3>
                        <div class="summary-item">
                            <span class="summary-label">Primary Skills:</span>
                            <span class="summary-value"><?php echo substr($user['skills'] ?? 'Not specified', 0, 50); ?>...</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Experience Level:</span>
                            <span class="summary-value">
                                <?php 
                                $exp = $user['experience'] ?? '';
                                if(strlen($exp) > 100) echo 'Experienced';
                                elseif(strlen($exp) > 50) echo 'Intermediate';
                                else echo 'Entry Level';
                                ?>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Education:</span>
                            <span class="summary-value"><?php echo substr($user['education'] ?? 'Not specified', 0, 30); ?>...</span>
                        </div>
                    </div>
                    
                    <div class="ai-actions">
                        <button class="btn btn-primary btn-block" id="refreshRecommendations">
                            <i class="fas fa-sync-alt"></i> Refresh Recommendations
                        </button>
                        <button class="btn btn-outline btn-block" id="chatWithAI">
                            <i class="fas fa-comment-alt"></i> Chat with Career AI
                        </button>
                        <button class="btn btn-secondary btn-block" id="generateReport">
                            <i class="fas fa-file-pdf"></i> Generate Career Report
                        </button>
                    </div>
                </div>
                
                <div class="ai-content">
                    <div class="ai-welcome">
                        <div class="ai-avatar">
                            <i class="fas fa-robot fa-3x"></i>
                        </div>
                        <div class="ai-message">
                            <h3>Hello <?php echo $_SESSION['user_name']; ?>! 👋</h3>
                            <p>I've analyzed your profile and here are my personalized recommendations for your career growth. </p>
                        </div>
                    </div>
                    
                    <div class="recommendations-grid">
                        <?php foreach($recommendations as $index => $rec): ?>
                        <div class="recommendation-card">
                            <div class="rec-header">
                                <div class="rec-icon">
                                    <?php if($rec['type'] == 'career_path'): ?>
                                        <i class="fas fa-route"></i>
                                    <?php elseif($rec['type'] == 'skill_gap'): ?>
                                        <i class="fas fa-puzzle-piece"></i>
                                    <?php else: ?>
                                        <i class="fas fa-graduation-cap"></i>
                                    <?php endif; ?>
                                </div>
                                <h3><?php echo $rec['title']; ?></h3>
                            </div>
                            
                            <div class="rec-body">
                                <p><?php echo $rec['description']; ?></p>
                                
                                <?php if(isset($rec['steps'])): ?>
                                    <div class="rec-steps">
                                        <h4>Recommended Steps:</h4>
                                        <ul>
                                            <?php foreach($rec['steps'] as $step): ?>
                                                <li><?php echo $step; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(isset($rec['resources'])): ?>
                                    <div class="rec-resources">
                                        <h4>Learning Resources:</h4>
                                        <div class="resource-tags">
                                            <?php foreach($rec['resources'] as $resource): ?>
                                                <span class="resource-tag"><?php echo $resource; ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(isset($rec['courses'])): ?>
                                    <div class="rec-courses">
                                        <h4>Recommended Courses:</h4>
                                        <?php foreach($rec['courses'] as $course): ?>
                                            <div class="course-item">
                                                <strong><?php echo $course['name']; ?></strong>
                                                <span><?php echo $course['platform']; ?></span>
                                                <small><?php echo $course['duration']; ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rec-actions">
                                <button class="btn btn-outline save-rec" data-rec="<?php echo $index; ?>">
                                    <i class="far fa-bookmark"></i> Save
                                </button>
                                <button class="btn btn-primary explore-rec">
                                    <i class="fas fa-search"></i> Explore
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="ai-chat-section" style="display: none;">
                        <div class="chat-header">
                            <h3><i class="fas fa-comments"></i> Career AI Assistant</h3>
                            <button class="btn btn-outline close-chat">Close Chat</button>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages">
                            <div class="message ai-message">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <p>Hello! I'm your Career AI assistant. How can I help you with your career journey today?</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-input">
                            <input type="text" id="chatInput" placeholder="Ask me anything about careers, skills, or jobs...">
                            <button id="sendMessage" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Career Report Modal -->
            <div id="reportModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Generate Career Report</h2>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="report-options">
                            <div class="option-card">
                                <div class="option-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4>Basic Report</h4>
                                <p>Career recommendations and skill analysis</p>
                                <button class="btn btn-primary generate-report" data-type="basic">
                                    Generate PDF
                                </button>
                            </div>
                            
                            <div class="option-card">
                                <div class="option-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h4>Detailed Analysis</h4>
                                <p>Market trends and salary benchmarks</p>
                                <button class="btn btn-primary generate-report" data-type="detailed">
                                    Generate PDF
                                </button>
                            </div>
                            
                            <div class="option-card">
                                <div class="option-icon">
                                    <i class="fas fa-road"></i>
                                </div>
                                <h4>Career Roadmap</h4>
                                <p>Step-by-step 5-year plan</p>
                                <button class="btn btn-primary generate-report" data-type="roadmap">
                                    Generate PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/ai_guidance.js"></script>
</body>
</html>