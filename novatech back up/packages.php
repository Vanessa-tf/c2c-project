<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Packages - NovaTech FET College</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Important: same stylesheet as Subjects page -->
  <link rel="stylesheet" href="Css/style_subject.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light-beige">

  <!-- Header (identical to Subjects page) -->
  <header class="bg-white shadow-md">
    <div class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <img src="Images/ChatGPT Image Sep 15, 2025, 08_43_22 PM.png" alt="NovaTech Logo" class="h-20 w-auto"/>
          <span class="ml-4 text-2xl font-bold text-navy">
            <span class="logo-text">NovaTech FET</span>
            <span class="logo-college"> College</span>
          </span>
        </div>
        <nav class="hidden md:flex space-x-8">
          <a href="index.php" class="text-navy hover:text-gold font-medium">Home</a>
          <a href="Subjects.html" class="text-navy hover:text-gold font-medium">Subjects</a>
          <a href="packages.php" class="text-navy hover:text-gold font-medium">Packages</a>
          <a href="about.php" class="text-navy hover:text-gold font-medium">About Us</a>
          <a href="contact.html" class="text-navy hover:text-gold font-medium">Contact Us</a>
        </nav>
        <div class="flex items-center space-x-4">
          <a href="enroll.php" class="px-6 py-3 bg-gold text-navy font-bold rounded-lg hover:bg-yellow-500 transition">Enroll Now</a>
          <button class="md:hidden focus:outline-none">
            <i data-feather="menu" class="text-navy"></i>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Packages Hero Section -->
  <section class="relative text-center text-white py-24" style="background-image: url('images/LPH.jpeg'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 bg-gradient-to-r from-navy to-gold opacity-70"></div>
    <div class="relative z-10 container mx-auto px-6">
      <h1 class="text-5xl font-bold mb-4">Choose Your Learning Path</h1>
      <p class="text-lg">Flexible subscription plans designed to help you succeed in your matric rewrite journey.</p>
    </div>
  </section>
<style>
/* Root variables from Subjects.html CSS */
:root {
    --navy: #1e3a8a;
    --gold: #facc15;
    --beige: #f5f5dc;
}

/* Hero Section with Gradient */
.hero {
    background-color: var(--navy);
    color: white;
    text-align: center;
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.hero:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, var(--navy), var(--gold));
    opacity: 0.7;
}

.hero .hero-content {
    position: relative;
    z-index: 2;
}

.hero-content h1 {
    font-size: 3em;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    font-weight: 700;
}

.hero-content p {
    font-size: 1.5em;
    margin-bottom: 30px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    font-weight: 400;
    line-height: 1.6;
}

/* Active navigation link */
nav ul li a.active {
    color: var(--gold);
    font-weight: 600;
}

/* Flexibility section */
.flexibility-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.flexibility-card {
    text-align: center;
    padding: 30px;
    background-color: var(--beige);
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.flexibility-card:hover {
    transform: translateY(-10px);
}

.flexibility-icon {
    font-size: 2.5em;
    color: var(--navy);
    margin-bottom: 15px;
}

/* Package badge */
.package-badge {
    position: absolute;
    top: -10px;
    right: 20px;
    background-color: var(--gold);
    color: var(--navy);
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8em;
}

.package-card {
    position: relative;
    padding-top: 40px;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.package-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.package-card.popular {
    transform: scale(1.05);
    border: 2px solid var(--gold);
}

.package-card.popular:hover {
    transform: scale(1.05) translateY(-10px);
}

.package-card ul li {
    display: flex;
    align-items: center;
    gap: 10px;
}

.package-card ul li i.fa-check-circle {
    color: #22c55e;
}

.package-card ul li i.fa-times-circle {
    color: #ef4444;
}

/* FAQ Section */
.faq-section {
    padding: 80px 0;
    background-color: var(--beige);
}

.faq-container {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    background-color: white;
    margin-bottom: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.faq-item h3 {
    padding: 20px;
    margin: 0;
    color: var(--navy);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.1em;
}

.faq-item h3 i {
    transition: transform 0.3s;
}

.faq-answer {
    padding: 0 20px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

.faq-answer.active {
    padding: 0 20px 20px;
    max-height: 200px;
}

.faq-answer p {
    margin: 0;
    color: #333;
}

/* Button Styles to match Subjects.html */
.package-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 25px;
}

.package-buttons .btn {
    padding: 12px 25px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-align: center;
    display: block;
    width: 100%;
    box-sizing: border-box;
}

.package-buttons .btn-primary {
    background-color: var(--gold);
    color: var(--navy);
    border: 2px solid var(--gold);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.package-buttons .btn-primary:hover {
    background-color: #eab308;
    border-color: #eab308;
    transform: scale(1.05);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    text-decoration: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero {
        padding: 60px 0;
    }

    .hero-content h1 {
        font-size: 2.5em;
    }

    .hero-content p {
        font-size: 1.2em;
    }
    
    .package-card.popular {
        transform: scale(1);
    }
    
    .package-card.popular:hover {
        transform: translateY(-10px);
    }
    
    .flexibility-info {
        grid-template-columns: 1fr;
    }
    
    .package-buttons {
        flex-direction: column;
    }
    
    .package-buttons .btn {
        width: 100%;
    }
    
    .faq-item h3 {
        font-size: 1em;
        padding: 15px;
    }
    
    .faq-answer {
        padding: 0 15px;
    }
    
    .faq-answer.active {
        padding: 0 15px 15px;
    }
}
</style>
</head>
<body>
    <!-- Subscription Flexibility Info -->
    <section class="about">
        <div class="container">
            <div class="section-title">
                <h2>Flexible Learning Options</h2>
                <p>We understand that your needs may change as you progress</p>
            </div>
            <div class="about-content">
                <div class="flexibility-info">
                    <div class="flexibility-card">
                        <div class="flexibility-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3>Change Anytime</h3>
                        <p>Upgrade or downgrade your subscription plan at any time to match your changing needs and budget.</p>
                    </div>
                    <div class="flexibility-card">
                        <div class="flexibility-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3>Cancel Anytime</h3>
                        <p>No long-term commitments. Cancel your subscription anytime without penalties or hidden fees.</p>
                    </div>
                    <div class="flexibility-card">
                        <div class="flexibility-icon">
                            <i class="fas fa-lock-open"></i>
                        </div>
                        <h3>No Lock-in Contracts</h3>
                        <p>All our plans are month-to-month with no mandatory lock-in periods or contracts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section class="packages">
        <div class="container">
            <div class="section-title">
                <h2>Our Subscription Packages</h2>
                <p>Choose a plan that fits your learning needs and budget.</p>
            </div>
            <div class="packages-grid">
                <div class="package-card">
                    <div class="package-badge">Most Affordable</div>
                    <h3>Basic Package</h3>
                    <p class="price">Free</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Access to 1 Subject</li>					
                        <li><i class="fas fa-check-circle"></i> Access to Learning Content</li>
                        <li><i class="fas fa-check-circle"></i> Access to Digital Library (past papers and memos, textbooks)</li>
                        <li><i class="fas fa-times-circle"></i> Live and Recorded lessons</li>
                        <li><i class="fas fa-times-circle"></i> Different Learning Styles (Auditory, Reading and Writing, Visual)</li>	
                        <li><i class="fas fa-times-circle"></i> Mock Exam with Teacher/Tutor Feedback</li>	
                        <li><i class="fas fa-times-circle"></i> Tutor support</li>
                        <li><i class="fas fa-times-circle"></i> Social Chatroom</li>
                    </ul>
                    <div class="package-buttons">
                        <a href="enroll.php" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
                <div class="package-card popular">
                    <div class="package-badge">Most Popular</div>
                    <h3>Standard Package</h3>
                    <p class="price">R699/month</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Access to 1-2 Subjects</li>
                        <li><i class="fas fa-check-circle"></i> Access to Learning Content</li>
                        <li><i class="fas fa-check-circle"></i> Access to Digital Library (past papers and memos, textbooks, study guides)</li>
                        <li><i class="fas fa-check-circle"></i> Live and Recorded lessons</li>
                        <li><i class="fas fa-check-circle"></i> Different Learning Styles (Auditory, Reading and Writing, Visual)</li>	
                        <li><i class="fas fa-check-circle"></i> Progress tracking</li>
                        <li><i class="fas fa-times-circle"></i> Tutor support</li>
                        <li><i class="fas fa-times-circle"></i> Social Chatroom</li>
                    </ul>
                    <div class="package-buttons">
                        <a href="enroll.php" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
                <div class="package-card">
                    <div class="package-badge">Most Comprehensive</div>
                    <h3>Premium Package</h3>
                    <p class="price">R1 199/month</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Access to 1-4 Subjects</li>
                        <li><i class="fas fa-check-circle"></i> Access to Learning Content</li>
                        <li><i class="fas fa-check-circle"></i> Access to Digital Library (past papers and memos, textbooks, study guides)</li>
                        <li><i class="fas fa-check-circle"></i> Live and Recorded lessons</li>
                        <li><i class="fas fa-check-circle"></i> Different Learning Styles (Auditory, Reading and Writing, Visual)</li>	
                        <li><i class="fas fa-check-circle"></i> Progress tracking</li>
                        <li><i class="fas fa-check-circle"></i> Tutor support</li>
                        <li><i class="fas fa-check-circle"></i> Social Chatroom</li>
                    </ul>
                    <div class="package-buttons">
                        <a href="enroll.php" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Everything you need to know about our subscription plans</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <h3>How do I change my subscription plan? <i class="fas fa-chevron-down"></i></h3>
                    <div class="faq-answer">
                        <p>You can change your plan at any time from your account dashboard. Simply go to the "Subscription" section and select the plan you want to switch to. The changes will take effect at the start of your next billing cycle.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3>Can I cancel my subscription anytime? <i class="fas fa-chevron-down"></i></h3>
                    <div class="faq-answer">
                        <p>Yes, you can cancel your subscription at any time without any cancellation fees. After cancellation, you'll retain access to the paid features until the end of your current billing period.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3>What payment methods do you accept? <i class="fas fa-chevron-down"></i></h3>
                    <div class="faq-answer">
                        <p>We accept all major credit cards, debit cards, and PayPal. We also support direct EFT payments for South African students.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3>Will I get a refund if I cancel? <i class="fas fa-chevron-down"></i></h3>
                    <div class="faq-answer">
                        <p>We offer prorated refunds for cancellations made within the first 7 days of subscription. After that, we don't offer refunds but you won't be charged again after cancellation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="apply" class="py-20 bg-navy text-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Start Your Journey?</h2>
                <p class="text-xl mb-8">Join NovaTech FET College and unlock your potential with our exceptional education programs.</p>
                
                <div class="relative max-w-md mx-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-feather="mail" class="text-gray-400"></i>
                    </div>
                    <input type="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gold focus:border-gold block w-full pl-10 p-2.5" placeholder="Enter your email" required>
                </div>
                
                <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                    <a href="enroll.php" class="bg-gold hover:bg-yellow-600 text-navy font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105">
                        Apply Now
                    </a>
                    <a href="contact.html" class="bg-transparent hover:bg-white hover:text-navy border-2 border-white text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105">
                        Contact Us
                    </a>
                </div>
                
                <div class="mt-12 flex justify-center">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gold rounded-lg blur opacity-75 animate-pulse"></div>
                        <div class="relative bg-white text-navy px-6 py-3 rounded-lg font-bold">
                            Limited Spaces Available - Apply Today!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    	 <!-- Stats Section -->
    <section class="py-16 bg-gold text-navy">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8 text-center">
                <div data-aos="fade-up" data-aos-delay="100">
                    
                    <div class="text-lg font-medium">Training</div>
                </div>
                <div data-aos="fade-up" data-aos-delay="200">
                    
                    <div class="text-lg font-medium">Students Enrolled</div>
                </div>
                <div data-aos="fade-up" data-aos-delay="300">
                   
                    <div class="text-lg font-medium">Qualified Educators</div>
                </div>
                <div data-aos="fade-up" data-aos-delay="400">
                    
                    <div class="text-lg font-medium">Years Experience</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer - Exact copy from subjects.html -->
    <footer class="bg-navy text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <div class="flex items-center">
                        <img src="Images/ChatGPT Image Sep 15, 2025, 08_43_22 PM.png" alt="NovaTech Logo" class="h-16 w-auto"/>
                        <span class="ml-4 text-2xl font-bold">
                            <span>NovaTech FET</span>
                            <span class="text-gold"> College</span>
                        </span>
                    </div>
                    <p>Empowering matric rewrite students with quality education.</p>
                    <p>NovaTech - Rewriting Futures, Transforming Lives</p>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gold">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="hover:text-gold transition">Home</a></li>
                        <li><a href="Subjects.html" class="hover:text-gold transition">Subjects</a></li>
                        <li><a href="packages.php" class="hover:text-gold transition">Packages</a></li>
                        <li><a href="about.php" class="hover:text-gold transition">About Us</a></li>
                        <li><a href="contact.html" class="hover:text-gold transition">Contact Us</a></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gold">Subjects</h3>
                    <ul class="space-y-2">
                        <li><a href="Subjects.html" class="hover:text-gold transition">Mathematics</a></li>
                        <li><a href="Subjects.html" class="hover:text-gold transition">Physical Science</a></li>
                        <li><a href="Subjects.html" class="hover:text-gold transition">English</a></li>
                        <li><a href="Subjects.html" class="hover:text-gold transition">CAT</a></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gold">Contact Us</h3>
                    <div class="flex items-start space-x-3">
                        <i data-feather="map-pin" class="mt-1"></i>
                        <p>123 Education Street, Midrand, 1685</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i data-feather="phone" class="mt-1"></i>
                        <p>+27 66 193 1982</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i data-feather="mail" class="mt-1"></i>
                        <a href="mailto:info@novatechfet.co.za" class="hover:text-gold transition">info@novatechfet.co.za</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-12 pt-8 text-center">
                <p>&copy; 2025 NovaTech FET College. All Rights Reserved.</p> 
				<p>|Designed by STEMinists|</p>
            </div>
        </div>
    </footer>
	
    <script src="js/script.js"></script>
    <script>
	    AOS.init();
    feather.replace();
	
        // FAQ toggle functionality
        document.querySelectorAll('.faq-item h3').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const icon = question.querySelector('i');
                
                answer.classList.toggle('active');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            });
        });
    </script>
</body>
</html>