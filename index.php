<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Updated title to reflect asset management senior project -->
    <title>Asset Management System | Senior Project</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <?php include_once 'navbar.php'; ?>
</head>

<body>
    <!-- Removed entire header/navigation section -->

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <!-- Updated hero content to focus on asset management senior project -->
            <h1 class="hero-title">Asset Management System</h1>
            <p class="hero-subtitle">A comprehensive digital solution for tracking, managing, and optimizing organizational assets. Developed as a senior capstone project to demonstrate modern web development and database management skills.</p>
        </div>
        <!-- Removed hero-stats section entirely -->
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <!-- Updated section to describe the senior project -->
                <h2>Senior Capstone Project</h2>
                <p>This asset management system represents the culmination of computer science studies, showcasing full-stack development capabilities and real-world problem-solving skills.</p>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4" />
                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3" />
                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3" />
                            <path d="M3 12v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6" />
                        </svg>
                    </div>
                    <!-- Updated values to reflect project goals -->
                    <h3>Innovation</h3>
                    <p>Modern web technologies and best practices implemented to create an efficient asset tracking solution.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" />
                            <path d="M2 17l10 5 10-5" />
                            <path d="M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <h3>Scalability</h3>
                    <p>Designed with growth in mind, supporting organizations of various sizes and asset types.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <!-- Updated to describe system features instead of financial services -->
                <h2>System Features</h2>
                <p>Comprehensive asset management capabilities designed to streamline organizational operations and improve asset visibility.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <!-- Updated service cards to reflect asset management features -->
                    <h3>Asset Tracking</h3>
                    <p>Real-time monitoring and tracking of all organizational assets with detailed audit trails and location management.</p>
                    <ul>
                        <li>Real-time Location Tracking</li>
                        <li>Asset History & Audit Trails</li>
                        <li>Barcode/QR Code Integration (Phone Application)</li>
                    </ul>
                </div>
                <div class="service-card">
                    <h3>Smart Inventory Management</h3>
                    <ul>
                        <li>Kuali Integration</li>
                        <li>Automatic Updating</li>
                        <li>Reporting & Analytics</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Removed entire testimonials section -->

    <!-- Removed CTA section with buttons -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <!-- Updated footer content for senior project -->
                    <h3>Asset Management System</h3>
                    <p>Senior capstone project demonstrating full-stack development and system design capabilities.</p>
                </div>
                <div class="footer-section">
                    <h4>Features</h4>
                    <ul>
                        <li>Asset Tracking</li>
                        <li>Inventory Management</li>
                        <li>Reporting & Analytics</li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Project</h4>
                    <ul>
                        <li><a href="#about">About Project</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Distribution Services<br>distribution@csub.edu</p>
                    <p>Senior Project 2025<br>Asset Management System</p>
                </div>
            </div>
            <div class="footer-bottom">
                <!-- Updated copyright for senior project -->
                <p>&copy; 2025 Asset Management System - Senior Project. All rights reserved.</p>

            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>
