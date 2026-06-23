<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - NovaTech FET College</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
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
    
    .founder-image {
      width: 300px;
      height: 350px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    @media (max-width: 768px) {
      .founder-image {
        width: 100%;
        height: auto;
        margin-bottom: 1.5rem;
      }
    }
  </style>
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

  <!-- About Section -->
  <section class="about-page py-16">
    <div class="container mx-auto px-6">
      <div class="text-center mb-12" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-navy mb-4">About NovaTech FET College</h2>
        <p class="text-lg text-gray-700 max-w-2xl mx-auto">Empowering students through innovative education and stigma-free learning.</p>
      </div>

      <div class="space-y-8">
        <!-- Our Story Section with Image -->
        <div data-aos="fade-up" data-aos-delay="100">
          <h3 class="text-2xl font-semibold text-navy mb-6">Our Story</h3>
          <div class="flex flex-col md:flex-row gap-8 items-start">
            <div class="md:w-1/3">
              <img src="Images/Oyama.jpeg" alt="Mr. Oyama Valashiya - Founder & CEO" class="founder-image">
            </div>
            <div class="md:w-2/3">
              <p class="text-gray-600 mb-4">Mr Oyama Valashiya is a passionate South African entrepreneur who is deeply committed to uplifting the youth of South Africa by providing accessible, high-quality educational opportunities. With a profound understanding of the challenges that matric rewrites face, Mr Valashiya is driven by a vision to empower students from diverse backgrounds.</p>
              <p class="text-gray-600">NovaTech FET College has a passion for fostering academic success and addressing the unique educational needs of South African Learners, especially in underserved communities, empowering students to achieve academic success and unlock opportunities for further education or employment.</p>
            </div>
          </div>
        </div>

        <div data-aos="fade-up" data-aos-delay="200">
          <h3 class="text-2xl font-semibold text-navy mb-2">Our Mission</h3>
          <p class="text-gray-600">To empower South African students by delivering a stigma-free, flexible, and affordable online learning experience that rewrites futures and unlocks new opportunities.</p>
        </div>

        <div data-aos="fade-up" data-aos-delay="300">
          <h3 class="text-2xl font-semibold text-navy mb-2">Our Values</h3>
          <ul class="list-disc list-inside text-gray-600 space-y-2">
            <li><strong class="text-navy">Inclusivity:</strong> Welcoming all students regardless of background.</li>
            <li><strong class="text-navy">Excellence:</strong> Committing to the highest educational standards.</li>
            <li><strong class="text-navy">Accessibility:</strong> Ensuring learning is available anytime, anywhere.</li>
            <li><strong class="text-navy">Support:</strong> Providing a nurturing community for student success.</li>
          </ul>
        </div>
		
		        <!-- Option 2: Learning Benefits -->
        <div data-aos="fade-up" data-aos-delay="500">
          <h3 class="text-2xl font-semibold text-navy mb-6">Why Choose Our Learning Platform?</h3>
          <div class="grid md:grid-cols-2 gap-8">
            <div class="benefit-card bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-start mb-4">
                <div class="bg-gold p-3 rounded-full mr-4">
                  <i class="fas fa-user-clock text-navy"></i>
                </div>
                <h4 class="text-xl font-bold text-navy">Self-Paced Learning</h4>
              </div>
              <p class="text-gray-600">Study at your own convenience with 24/7 access to all learning materials, perfect for working students or those with busy schedules.</p>
            </div>
            <div class="benefit-card bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-start mb-4">
                <div class="bg-gold p-3 rounded-full mr-4">
                  <i class="fas fa-chalkboard-teacher text-navy"></i>
                </div>
                <h4 class="text-xl font-bold text-navy">Expert Instruction</h4>
              </div>
              <p class="text-gray-600">Learn from experienced educators who specialize in matric curriculum and understand the challenges of rewrite students.</p>
            </div>
            <div class="benefit-card bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-start mb-4">
                <div class="bg-gold p-3 rounded-full mr-4">
                  <i class="fas fa-chart-bar text-navy"></i>
                </div>
                <h4 class="text-xl font-bold text-navy">Progress Analytics</h4>
              </div>
              <p class="text-gray-600">Track your performance with detailed analytics that highlight strengths and areas needing improvement.</p>
            </div>
            <div class="benefit-card bg-white p-6 rounded-xl shadow-lg">
              <div class="flex items-start mb-4">
                <div class="bg-gold p-3 rounded-full mr-4">
                  <i class="fas fa-comments text-navy"></i>
                </div>
                <h4 class="text-xl font-bold text-navy">Community Support</h4>
              </div>
              <p class="text-gray-600">Connect with fellow students through discussion forums and study groups to enhance your learning experience.</p>
            </div>
          </div>
        </div>

        <!-- Option 3: Accreditation & Recognition -->
        <div data-aos="fade-up" data-aos-delay="600">
          <h3 class="text-2xl font-semibold text-navy mb-6">Accreditation & Recognition</h3>
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="flex flex-col md:flex-row items-center md:items-start">
              <div class="bg-gold p-4 rounded-full mb-4 md:mb-0 md:mr-6">
                <i class="fas fa-award text-navy text-3xl"></i>
              </div>
              <div>
                <h4 class="text-xl font-bold text-navy mb-2">Quality Education Partner</h4>
                <p class="text-gray-600">NovaTech FET College is committed to providing education that meets national standards. Our curriculum is aligned with the South African National Curriculum Statement, ensuring our students receive quality education that prepares them for success.</p>
                <p class="mt-3 text-gray-600">We are in the process of accreditation with the appropriate educational bodies to further validate our programs and provide our students with recognized qualifications.</p>
              </div>
            </div>
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
          <div class="text-4xl font-bold mb-2"></div>
          <div class="text-lg font-medium">Core Subjects</div>
        </div>
        <div data-aos="fade-up" data-aos-delay="200">
          <div class="text-4xl font-bold mb-2"></div>
          <div class="text-lg font-medium">Students Enrolled</div>
        </div>
        <div data-aos="fade-up" data-aos-delay="300">
          <div class="text-4xl font-bold mb-2"></div>
          <div class="text-lg font-medium">Qualified Educators</div>
        </div>
        <div data-aos="fade-up" data-aos-delay="400">
          <div class="text-4xl font-bold mb-2"></div>
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

  <!-- Scripts -->
  <script>
    AOS.init();
    feather.replace();
  </script>
</body>
</html>