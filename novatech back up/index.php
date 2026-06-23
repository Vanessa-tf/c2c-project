<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaTech FET College - Matric Rewrite LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1e3a8a;
            --gold: #fbbf24;
            --beige: #f5f1e3;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .bg-navy {
            background-color: var(--navy);
        }
        
        .bg-gold {
            background-color: var(--gold);
        }
        
        .bg-light-beige {
            background-color: var(--beige);
        }
        
        .text-navy {
            color: var(--navy);
        }
        
        .text-gold {
            color: var(--gold);
        }
        
        .logo-text {
            font-weight: 700;
        }
        
        .logo-college {
            color: var(--gold);
        }
        
        .subject-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .hidden-content {
            display: none;
        }
        
        .hidden-content.visible {
            display: block;
        }
        
        .testimonial-card {
            background-color: #f8fafc;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-card {
            background-color: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--navy);
            margin-bottom: 1rem;
        }
        
        .step {
            text-align: center;
            padding: 1.5rem;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background-color: var(--gold);
            color: var(--navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .package-card {
            background-color: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .package-card:hover {
            transform: translateY(-5px);
        }
        
        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--navy);
            margin: 1rem 0;
        }
    </style>
</head>
<body class="bg-light-beige">
    <!-- Header -->
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

    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0 overflow-hidden">
            <img src="images/hero.jpg" alt="Background" class="w-full h-full object-cover filter blur-sm opacity-20">
        </div>
        <div class="container mx-auto px-6 py-24 md:py-32 relative z-10">
            <div class="max-w-3xl mx-auto text-center" data-aos="fade-up">
                <h1 class="text-5xl md:text-7xl font-bold mb-6">Rewrite Your Matric, Rewrite Your Future</h1>
                <p class="text-xl mb-12">Access quality learning resources, expert tutors, and a supportive community to improve your matric results and unlock new opportunities.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="enroll.php" class="bg-gold hover:bg-yellow-600 text-navy font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105">
                        Get Started Today
                    </a>
                    <a href="Subjects.html" class="bg-transparent hover:bg-white hover:text-navy border-2 border-white text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105">
                        View Subjects
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute inset-0 opacity-20">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-navy"></div>
        </div>
    </div>

    <!-- About Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-navy mb-4">About NovaTech FET College</h2>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">Empowering South African students with flexible, stigma-free online learning.</p>
            </div>
            <div class="max-w-3xl mx-auto text-center" data-aos="fade-up" data-aos-delay="100">
                <p class="text-gray-600 text-lg">NovaTech FET College is dedicated to providing an innovative Learning Management System (LMS) that allows students to rewrite their matric comfortably from home.</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-beige">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-navy mb-4">Why Choose NovaTech LMS?</h2>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">Our platform is designed specifically for matric rewrite students, offering comprehensive tools for success.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon"><i class="fas fa-book-open"></i></div>
                    <h3 class="text-xl font-bold text-navy mb-2">Past Exam Papers</h3>
                    <p class="text-gray-600">Access a comprehensive database of NSC exam papers with detailed solutions.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon"><i class="fas fa-video"></i></div>
                    <h3 class="text-xl font-bold text-navy mb-2">Live & Recorded Lessons</h3>
                    <p class="text-gray-600">Attend live classes or watch recordings at your convenience.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="text-xl font-bold text-navy mb-2">Progress Tracking</h3>
                    <p class="text-gray-600">Monitor your improvement with personalized analytics.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3 class="text-xl font-bold text-navy mb-2">Peer Community</h3>
                    <p class="text-gray-600">Collaborate and motivate each other in our forums.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="text-xl font-bold text-navy mb-2">Mobile Access</h3>
                    <p class="text-gray-600">Study anytime, anywhere on any device.</p>
                </div>
                
                <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <h3 class="text-xl font-bold text-navy mb-2">Affordable Packages</h3>
                    <p class="text-gray-600">Flexible plans to fit your budget and needs.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-navy mb-4">How It Works</h2>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">Simple steps to start your matric rewrite journey.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <div class="step" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-number">1</div>
                    <h3 class="text-xl font-bold text-navy mb-2">Register</h3>
                    <p class="text-gray-600">Create an account and select subjects.</p>
                </div>
                
                <div class="step" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-number">2</div>
                    <h3 class="text-xl font-bold text-navy mb-2">Choose Package</h3>
                    <p class="text-gray-600">Select a plan that suits you.</p>
                </div>
                
                <div class="step" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-number">3</div>
                    <h3 class="text-xl font-bold text-navy mb-2">Start Learning</h3>
                    <p class="text-gray-600">Access resources and track progress.</p>
                </div>
                
                <div class="step" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-number">4</div>
                    <h3 class="text-xl font-bold text-navy mb-2">Excel</h3>
                    <p class="text-gray-600">Achieve your academic goals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section class="py-20 bg-beige">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-navy mb-4">Our Packages</h2>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">Choose a plan that fits your learning needs and budget.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="package-card" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-2xl font-bold text-navy mb-4">Basic Package</h3>
                    <p class="price">Free</p>
                    <a href="packages.php" class="mt-4 bg-navy text-white py-2 px-6 rounded-lg hover:bg-opacity-90 transition inline-block">
                        Learn More
                    </a>
                </div>
                
                <div class="package-card" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold text-navy mb-4">Standard Plan</h3>
                    <p class="price">R699/month</p>
                    <a href="packages.php" class="mt-4 bg-navy text-white py-2 px-6 rounded-lg hover:bg-opacity-90 transition inline-block">
                        Learn More
                    </a>
                </div>
                
                <div class="package-card" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="text-2xl font-bold text-navy mb-4">Premium Package</h3>
                    <p class="price">R1 199/month</p>
                    <a href="packages.php" class="mt-4 bg-navy text-white py-2 px-6 rounded-lg hover:bg-opacity-90 transition inline-block">
                        Learn More
                    </a>
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
                <p>&copy; 2025 NovaTech FET College. All Rights Reserved. | Designed by STEMinists |</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Mobile menu toggle
        document.querySelector('header button').addEventListener('click', function() {
            const nav = document.querySelector('header nav');
            nav.classList.toggle('hidden');
            nav.classList.toggle('flex');
            nav.classList.toggle('flex-col');
            nav.classList.toggle('absolute');
            nav.classList.toggle('top-16');
            nav.classList.toggle('left-0');
            nav.classList.toggle('right-0');
            nav.classList.toggle('bg-white');
            nav.classList.toggle('shadow-lg');
            nav.classList.toggle('p-4');
            nav.classList.toggle('space-y-4');
            nav.classList.toggle('space-x-8');
        });

        // Replace icons
        feather.replace();
    </script>
</body>
</html>