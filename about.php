<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | JeevanSetu</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <main class="container">
        <section class="about-section">
            <h1>About JeevanSetu</h1>
            <p class="tagline">Bridging the gap between donors and those in need across India</p>
            
            <div class="about-content">
                <div class="about-text">
                    <h2>Our Mission</h2>
                    <p>JeevanSetu was founded in 2023 with a simple yet powerful mission: to create a national network that connects blood and organ donors with patients in emergency situations. Our platform eliminates geographical barriers and reduces the time it takes to find compatible donors, ultimately saving more lives.</p>
                    
                    <h2>Why We Exist</h2>
                    <p>Every year, thousands of lives are lost in India due to the unavailability of blood or organs at the right time. The traditional donation systems often struggle with:</p>
                    <ul>
                        <li>Limited donor databases</li>
                        <li>Geographical constraints</li>
                        <li>Lack of real-time availability information</li>
                        <li>Inefficient matching systems</li>
                    </ul>
                    <p>JeevanSetu addresses all these challenges through our technology-driven platform.</p>
                    
                    <h2>Our Technology</h2>
                    <p>Our smart matching algorithm considers multiple factors to find the best possible donor match:</p>
                    <div class="features">
                        <div class="feature">
                            <h3>Location-Based</h3>
                            <p>Prioritizes donors closest to the requester</p>
                        </div>
                        <div class="feature">
                            <h3>Real-Time Status</h3>
                            <p>Tracks donor availability in real-time</p>
                        </div>
                        <div class="feature">
                            <h3>Comprehensive Matching</h3>
                            <p>Matches by blood group, organ type, and other medical factors</p>
                        </div>
                    </div>
                </div>
                
                <div class="about-image">
                    <img src="assets/about-image.jpg" alt="Team working together">
                </div>
            </div>
            
            <div class="team-section">
                <h2>Our Team</h2>
                <p>JeevanSetu is developed and maintained by a passionate team of:</p>
                <ul>
                    <li>Healthcare professionals</li>
                    <li>Software engineers</li>
                    <li>Social workers</li>
                    <li>Volunteers across India</li>
                </ul>
            </div>
            
            <div class="partners-section">
                <h2>Our Partners</h2>
                <div class="partners">
                    <img src="assets/partner1.png" alt="Partner 1">
                    <img src="assets/partner2.png" alt="Partner 2">
                    <img src="assets/partner3.png" alt="Partner 3">
                </div>
            </div>
            
            <div class="cta-section">
                <h2>Join Our Movement</h2>
                <p>Whether you want to become a donor, volunteer, or partner with us, we'd love to hear from you.</p>
                <div class="cta-buttons">
                    <a href="auth/register.php" class="btn btn-primary">Register as Donor</a>
                    <a href="contact.php" class="btn btn-secondary">Contact Us</a>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'components/footer.php'; ?>

    <script>
          window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`; // 20px extra buffer
  });
    </script>
</body>
</html>