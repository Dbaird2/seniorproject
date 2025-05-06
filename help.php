<?php
include_once("navbar.php")

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }
        
        a {
            text-decoration: none;
            color: #1a73e8;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header styles */
        header {
            background-color: #1a73e8;
            color: white;
            padding: 40px 0;
        }
        
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .search-container {
            position: relative;
            max-width: 600px;
        }
        
        .search-container input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #1a73e8;
        }
        
        /* Main content styles */
        main {
            padding: 40px 0;
        }
        
        section {
            margin-bottom: 50px;
        }
        
        h2 {
            color: #1a73e8;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        /* Help categories */
        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .category-card {
            border: 1px solid #e0e9ff;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .category-card:hover {
            border-color: #1a73e8;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.1);
        }
        
        .category-card h3 {
            color: #1a73e8;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .category-card h3 i {
            margin-right: 10px;
        }
        
        .category-card p {
            color: #555;
            margin-bottom: 15px;
        }
        
        .category-card .link {
            color: #1a73e8;
            display: inline-flex;
            align-items: center;
        }
        
        .category-card .link i {
            margin-left: 5px;
        }
        
        /* FAQs - Updated to use links */
        .faq-list {
            list-style: none;
            border: 1px solid #e0e9ff;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .faq-list li {
            border-bottom: 1px solid #e0e9ff;
        }
        
        .faq-list li:last-child {
            border-bottom: none;
        }
        
        .faq-list a {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #1a73e8;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        
        .faq-list a:hover {
            background-color: #f5f9ff;
            text-decoration: none;
        }
        
        .faq-list a i {
            color: #1a73e8;
        }
        
        /* Contact section */
        .contact-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .contact-card {
            border: 1px solid #e0e9ff;
            border-radius: 8px;
            padding: 20px;
        }
        
        .contact-card h3 {
            color: #1a73e8;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .contact-card h3 i {
            margin-right: 10px;
        }
        
        .contact-card a {
            display: block;
            font-weight: 600;
            margin: 5px 0;
        }
        
        .contact-card .note {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
        }
        
        /* Footer */
        footer {
            background-color: #f5f9ff;
            border-top: 1px solid #e0e9ff;
            padding: 30px 0;
            text-align: center;
        }
        
        footer p {
            color: #1a73e8;
            margin-bottom: 10px;
        }
        
        footer .copyright {
            font-size: 0.9rem;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            header {
                padding: 30px 0;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            .categories, .contact-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1>How can we help you?</h1>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search for help topics...">
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="container">
        <!-- Help categories -->
        <section>
            <h2>Help Categories</h2>
            <div class="categories">
                <div class="category-card">
                    <h3><i class="fas fa-file-alt"></i> Documentation</h3>
                    <p>Browse our detailed documentation to find guides and references.</p>
                    <a href="#" class="link">View documentation <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="category-card">
                    <h3><i class="fas fa-comments"></i> Community Forum</h3>
                    <p>Join discussions and get help from our community members.</p>
                    <a href="#" class="link">Visit forum <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="category-card">
                    <h3><i class="fas fa-question-circle"></i> Support Tickets</h3>
                    <p>Create a support ticket for direct assistance from our team.</p>
                    <a href="#" class="link">Open ticket <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <!-- FAQs - Updated to use links -->
        <section>
            <h2>Frequently Asked Questions</h2>
            <ul class="faq-list">
                <li>
                    <a href="faq/reset-password.php">
                        How do I reset my password?
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </li>
                <li>
                    <a href="faq/system-requirements.php">
                        What are the system requirements?
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </li>
                <li>
                    <a href="faq/account-security.php">
                        How can I secure my account?
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </li>
                <li>
                    <a href="faq/excel-imports.php">
                        How do I audit?
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </li>
                <li>
                    <a href="faq/all-faqs.php">
                        View all FAQs
                        <i class="fas fa-list"></i>
                    </a>
                </li>
            </ul>
        </section>

        <!-- Contact information -->
        <section>
            <h2>Contact Us</h2>
            <div class="contact-cards">
                <div class="contact-card">
                    <h3><i class="fas fa-envelope"></i> Email Support</h3>
                    <p>For general inquiries and support:</p>
                    <a href="mailto:support@example.com">support@example.com</a>
                    <p>For billing questions:</p>
                    <a href="mailto:billing@example.com">billing@example.com</a>
                </div>

                <div class="contact-card">
                    <h3><i class="fas fa-phone"></i> Phone Support</h3>
                    <p>Customer Service:</p>
                    <a href="tel:+18001234567"></a>
                    <p>Technical Support:</p>
                    <a href="tel:+18009876543"></a>
                    <p class="note">Available Monday-Friday,</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Need more help? <a href="contact.php">Contact our support team</a></p>
            <p class="copyright">&copy; <span id="current-year"></span> . All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Set current year in footer
        document.getElementById('current-year').textContent = new Date().getFullYear();

        // Simple search functionality
        const searchInput = document.querySelector('.search-container input');
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                alert('Search functionality would go here. You searched for: ' + this.value);
            }
        });

        // Add hover effects for better interactivity
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
