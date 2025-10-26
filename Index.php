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
                <!-- [HERO IMAGE PLACEHOLDER] -->
                <img src="assets/dev.png" alt="Developer illustration">
            </div>
        </div>
    </section>

    <section id="skills" class="skills">
        <div class="container">
            <h2 class="section-title">My Skills</h2>
            <div class="skills-grid">
                <div class="skill-card">
                    <i class="fab fa-html5"></i>
                    <h3>HTML5</h3>
                    <p>Semantic markup for accessible websites</p>
                </div>
                <div class="skill-card">
                    <i class="fab fa-css3-alt"></i>
                    <h3>CSS3</h3>
                    <p>Modern styling with Flexbox and Grid</p>
                </div>
                <div class="skill-card">
                    <i class="fab fa-js"></i>
                    <h3>JavaScript</h3>
                    <p>Interactive web experiences</p>
                </div>
                <div class="skill-card">
                    <i class="fab fa-react"></i>
                    <h3>React</h3>
                    <p>Building dynamic single-page apps</p>
                </div>
                <div class="skill-card">
                    <i class="fab fa-php"></i>
                    <h3>PHP</h3>
                    <p>Server-side scripting for web development</p>
                </div>
                <div class="skill-card">
                    <i class="fas fa-database"></i>
                    <h3>MySQL</h3>
                    <p>Relational database management</p>
                </div>
                <div class="skill-card">
                    <i class="fab fa-wordpress"></i>
                    <h3>WordPress</h3>
                    <p>CMS development and customization</p>
                </div>
                <div class="skill-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Responsive Design</h3>
                    <p>Mobile-first approach</p>
                </div>
                <div class="skill-card">
                    <i class="fas fa-pencil-ruler"></i>
                    <h3>UI/UX Design</h3>
                    <p>User-centered interfaces</p>
                </div>
            </div>
        </div>
    </section>

    <section id="projects" class="projects">
        <div class="container">
            <h2 class="section-title">Featured Projects</h2>
            <div class="projects-grid">
                <div class="project-card">
                    <!-- [PROJECT 1 IMAGE PLACEHOLDER] -->
                    <img src="images/project1.jpg" alt="E-Commerce Website">
                    <div class="project-info">
                        <h3>E-Commerce Website</h3>
                        <p>A full-stack online store built with React, Node.js, and MongoDB.</p>
                        <div class="tech-tags">
                            <span>React</span>
                            <span>Node.js</span>
                            <span>MongoDB</span>
                        </div>
                        <div class="project-links">
                            <a href="#" class="btn btn-code">View Code</a>
                            <a href="#" class="btn btn-demo">Live Demo</a>
                        </div>
                    </div>
                </div>
                <div class="project-card">
                    <!-- [PROJECT 2 IMAGE PLACEHOLDER] -->
                    <img src="images/project2.jpg" alt="Task Management App">
                    <div class="project-info">
                        <h3>Task Management App</h3>
                        <p>A productivity app with drag-and-drop functionality and real-time updates.</p>
                        <div class="tech-tags">
                            <span>Vue.js</span>
                            <span>Firebase</span>
                            <span>Tailwind CSS</span>
                        </div>
                        <div class="project-links">
                            <a href="#" class="btn btn-code">View Code</a>
                            <a href="#" class="btn btn-demo">Live Demo</a>
                        </div>
                    </div>
                </div>
                <div class="project-card">
                    <!-- [PROJECT 3 IMAGE PLACEHOLDER] -->
                    <img src="images/project3.jpg" alt="Weather Dashboard">
                    <div class="project-info">
                        <h3>Weather Dashboard</h3>
                        <p>Real-time weather application with 5-day forecast and location search.</p>
                        <div class="tech-tags">
                            <span>JavaScript</span>
                            <span>API</span>
                            <span>CSS3</span>
                        </div>
                        <div class="project-links">
                            <a href="#" class="btn btn-code">View Code</a>
                            <a href="#" class="btn btn-demo">Live Demo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title">About Me</h2>
                <p>I'm a passionate fullstack developer with 3 years of experience creating modern web applications. My journey began when I built my first website in college, and I've been hooked ever since.</p>
                <p>I specialize in both frontend (React, JavaScript) and backend (PHP, Node.js) development, with expertise in database management (MySQL, MongoDB) and CMS platforms like WordPress.</p>
                <p>When I'm not coding, you can find me hiking, reading sci-fi novels, or experimenting with new recipes in the kitchen.</p>
                <a href="#" class="btn btn-primary">Download Resume</a>
            </div>
            <div class="about-image">
                <!-- [ABOUT IMAGE PLACEHOLDER] -->
                <img src="assets/profile.jpg" alt="Rubaiyat portrait">
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-content">
                <form class="contact-form">
                    <div class="form-group">
                        <input type="text" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <p><i class="fas fa-envelope"></i> hello@rubaiyatdev.com</p>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; <span id="current-year">2023</span> Rubaiyat. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script>
        // Update copyright year automatically
        document.getElementById('current-year').textContent = new Date().getFullYear();
    </script>
</body>
</html>