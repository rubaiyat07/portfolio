<?php
require_once 'config.php';
require_once 'visitor-tracker.php';

// Track this page visit
trackVisitor($conn);

// Fetch skills from database
$stmt = $conn->prepare("SELECT * FROM skills ORDER BY display_order ASC");
$stmt->execute();
$skills = $stmt->fetchAll();

// Fetch projects from database
$stmt = $conn->prepare("SELECT * FROM projects ORDER BY display_order ASC");
$stmt->execute();
$projects = $stmt->fetchAll();

// Function to get project technologies
function getProjectTech($project_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT technology FROM project_technologies WHERE project_id = ?");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rubaiyat | Fullstack Developer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <meta name="description" content="Portfolio of Rubaiyat, a fullstack web developer specializing in modern web applications. Explore my skills, projects, and get in touch.">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">Rubaiyat</a>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#skills">Skills</a></li>
                    <li><a href="#projects">Projects</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                    <div class="toggle-ball"></div>
                </div>
                <div class="hamburger">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
            </nav>
        </div>
    </header>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Hi, I'm <span class="highlight">Rubaiyat</span></h1>
                <h2>Fullstack Web Developer</h2>
                <p>I build beautiful, responsive websites that deliver results. Passionate about creating intuitive user experiences with clean code.</p>
                <div class="cta-buttons">
                    <a href="#projects" class="btn btn-primary">View My Work</a>
                    <a href="#contact" class="btn btn-secondary">Contact Me</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/dev.png" alt="Developer illustration">
            </div>
        </div>
    </section>

    <section id="skills" class="skills">
        <div class="container">
            <h2 class="section-title">My Skills</h2>
            <div class="skills-grid">
                <?php foreach($skills as $skill): ?>
                <div class="skill-card">
                    <i class="<?php echo htmlspecialchars($skill['icon_class']); ?>"></i>
                    <h3><?php echo htmlspecialchars($skill['title']); ?></h3>
                    <p><?php echo htmlspecialchars($skill['description']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="projects" class="projects">
        <div class="container">
            <h2 class="section-title">Featured Projects</h2>
            <div class="projects-grid">
                <?php foreach($projects as $project):
                    $technologies = getProjectTech($project['id']);
                    // Track project view
                    trackProjectView($conn, $project['id']);
                ?>
                <div class="project-card">
                    <img src="<?php echo htmlspecialchars($project['image_url'] ?: 'images/default-project.jpg'); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                    <div class="project-info">
                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p><?php echo htmlspecialchars($project['description']); ?></p>
                        <div class="tech-tags">
                            <?php foreach($technologies as $tech): ?>
                            <span><?php echo htmlspecialchars($tech); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="project-links">
                            <?php if($project['code_url']): ?>
                            <a href="<?php echo htmlspecialchars($project['code_url']); ?>" class="btn btn-code" target="_blank">View Code</a>
                            <?php endif; ?>
                            <?php if($project['demo_url']): ?>
                            <a href="<?php echo htmlspecialchars($project['demo_url']); ?>" class="btn btn-demo" target="_blank">Live Demo</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title">About Me</h2>
                <p>I'm a passionate PHP and Full Stack Developer with hands-on experience in building dynamic and scalable web applications. I completed my diploma course in <strong>Web Application Development</strong> under the IsDB-BISEW IT Scholarship Program, where I gained strong skills in PHP, MySQL, JavaScript, jQuery, and RESTful API integration.</p>
                <p>I love creating efficient, user-friendly systems — from backend logic to interactive frontend interfaces. I’ve also worked with tools like Bootstrap, Ajax, Git, and GitHub, and I’m always eager to learn new technologies and improve my craft.</p>
                <p>Beyond coding, I enjoy exploring creative ideas, writing, and spending time in calm, reflective moments that recharge my focus and creativity.</p>
                <a href="assets/Rubaiyat_Afreen.pdf" class="btn btn-primary" download>Download Resume</a>

            </div>
            <div class="about-image">
                <img src="assets/profile.jpg" alt="Rubaiyat portrait">
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-content">
                <form class="contact-form" method="POST" action="contact-handler.php">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <p><i class="fas fa-envelope"></i> rubaiyat97wd@gmail.com</p>
                    <p><i class="fas fa-phone"></i> (+880) 1945 - 559018</p>
                    <p><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</p>
                    <p>Follow Me:</p>
                    <div class="social-links">
                        <a href="https://github.com/rubaiyat07" target="_blank"><i class="fab fa-github"></i></a>
                        <a href="https://www.linkedin.com/in/rubaiyat07" target="_blank"><i class="fab fa-linkedin"></i></a>
                        <a href="https://www.behance.net/rubaiyatafreen" target="_blank"><i class="fab fa-behance"></i></a>
                        <a href="https://www.pinterest.com/rubaiyat07" target="_blank"><i class="fab fa-pinterest"></i></a>
                        <a href="https://wa.me/8801945559018" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.facebook.com/rubaiyat07" target="_blank"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/rubaiyat.07" target="_blank"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/script.js"></script>
    <script>
        // Update copyright year automatically
        document.getElementById('current-year').textContent = new Date().getFullYear();
    </script>
</body>
</html>