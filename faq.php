<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FAQs | JeevanSetu</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    html {
      scroll-behavior: smooth;
    }

    .container_faq {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      min-height: 80px;
    }

    .faq-section {
      padding: 40px 20px;
    }

    .faq-tabs {
      margin-bottom: 30px;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .faq-tab {
      display: inline-block;
      padding: 10px 20px;
      background: #eee;
      border: 2px solid #e63946;
      border-radius: 25px;
      color: #e63946;
      font-weight: bold;
      text-decoration: none;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .faq-tab:hover {
      background: #e63946;
      color: white;
      transform: scale(1.05);
    }

    .faq-tab.active {
      background: #e63946;
      color: white;
    }

    .faq-category {
      margin-bottom: 50px;
    }

    .faq-item {
      margin-bottom: 20px;
      border: 1px solid #ddd;
      border-radius: 5px;
      overflow: hidden;
    }

    .faq-question {
      padding: 15px;
      background: #f8f8f8;
      cursor: pointer;
      font-weight: bold;
      position: relative;
    }

    .faq-question:after {
      content: '+';
      position: absolute;
      right: 15px;
      font-size: 20px;
      transition: transform 0.3s ease;
    }

    .faq-question.active:after {
      content: '-';
      transform: rotate(180deg);
    }

    .faq-answer {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transition: max-height 0.5s ease, opacity 0.5s ease;
      background: #fff;
    }

    .faq-answer.show {
      max-height: 1000px;
      opacity: 1;
      padding: 15px;
    }

    .myths-section, .still-questions {
      margin-top: 40px;
    }

    .btn-primary {
      display: inline-block;
      padding: 10px 20px;
      background: #e63946;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      margin-top: 10px;
      transition: background 0.3s ease;
    }

    .btn-primary:hover {
      background: #c92d3b;
    }
  </style>
</head>
<body>
  <?php include 'components/header.php'; ?>

  <main class="container_faq">
    <section class="faq-section">
      <h1>Frequently Asked Questions</h1>

      <div class="faq-tabs">
        <a href="#general-faqs" class="faq-tab active">General</a>
        <a href="#blood-faqs" class="faq-tab">Blood Donation</a>
        <a href="#organ-faqs" class="faq-tab">Organ Donation</a>
        <a href="#technical-faqs" class="faq-tab">Technical</a>
      </div>

      <!-- GENERAL -->
      <div class="faq-category" id="general-faqs">
        <h2>General Questions</h2>

        <div class="faq-item">
          <div class="faq-question">What is JeevanSetu?</div>
          <div class="faq-answer">
            <p>JeevanSetu is a national network that connects blood and organ donors with patients in need...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">Is there any cost to use JeevanSetu?</div>
          <div class="faq-answer">
            <p>No, JeevanSetu is completely free for both donors and recipients...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">How do I register?</div>
          <div class="faq-answer">
            <p>You can register by clicking on the "Register" button at the top right corner...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">Is my personal information safe?</div>
          <div class="faq-answer">
            <p>Yes, we take privacy very seriously...</p>
          </div>
        </div>
      </div>

      <!-- BLOOD -->
      <div class="faq-category" id="blood-faqs">
        <h2>Blood Donation Questions</h2>

        <div class="faq-item">
          <div class="faq-question">Who can donate blood?</div>
          <div class="faq-answer">
            <p>Most healthy adults can donate blood if they...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">How often can I donate blood?</div>
          <div class="faq-answer">
            <p>Men can donate every 3 months, women every 4 months...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">Does blood donation hurt?</div>
          <div class="faq-answer">
            <p>You may feel a slight pinch when the needle is inserted...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">What should I do before donating blood?</div>
          <div class="faq-answer">
            <p>To prepare for blood donation: Get rest, eat well, stay hydrated...</p>
          </div>
        </div>
      </div>

      <!-- ORGAN -->
      <div class="faq-category" id="organ-faqs">
        <h2>Organ Donation Questions</h2>

        <div class="faq-item">
          <div class="faq-question">Which organs can be donated?</div>
          <div class="faq-answer">
            <p>Organs like kidneys, liver, heart, lungs, pancreas, and eyes can be donated...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">How does organ donation work?</div>
          <div class="faq-answer">
            <p>Living or deceased donors can donate organs depending on conditions...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">Will organ donation affect my health?</div>
          <div class="faq-answer">
            <p>For living donors: recovery is possible. No effect for deceased donors...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">Can I specify which organs I want to donate?</div>
          <div class="faq-answer">
            <p>Yes, you can specify while registering and update it anytime...</p>
          </div>
        </div>
      </div>

      <!-- TECHNICAL -->
      <div class="faq-category" id="technical-faqs">
        <h2>Technical Questions</h2>

        <div class="faq-item">
          <div class="faq-question">How does the matching algorithm work?</div>
          <div class="faq-answer">
            <p>We consider blood group, location, urgency, and other compatibility factors...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">What if I can't find a donor in my city?</div>
          <div class="faq-answer">
            <p>The system expands the search to nearby cities automatically...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">How do I update my donor profile?</div>
          <div class="faq-answer">
            <p>Log in → Go to Dashboard → Update Profile...</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">What if I need help using the platform?</div>
          <div class="faq-answer">
            <p>You can contact support via chat, email or toll-free helpline...</p>
          </div>
        </div>
      </div>

      <!-- MYTHS -->
      <div class="myths-section">
        <h2>Common Donation Myths</h2>

        <div class="myth-item">
          <h3>Myth: Blood donation makes you weak</h3>
          <p><strong>Fact:</strong> Your body replaces the blood in weeks. It's safe.</p>
        </div>

        <div class="myth-item">
          <h3>Myth: Organ donation disfigures the body</h3>
          <p><strong>Fact:</strong> It's done respectfully; open-casket funerals are possible.</p>
        </div>

        <div class="myth-item">
          <h3>Myth: You can't donate if you have tattoos</h3>
          <p><strong>Fact:</strong> You can donate after waiting 3–12 months depending on rules.</p>
        </div>

        <div class="myth-item">
          <h3>Myth: Older people can't donate organs</h3>
          <p><strong>Fact:</strong> Age doesn’t matter — organ quality does.</p>
        </div>
      </div>

      <!-- STILL QUESTIONS -->
      <div class="still-questions">
        <h2>Still have questions?</h2>
        <p>Contact our support team for any additional questions you may have.</p>
        <a href="contact.php" class="btn-primary">Contact Us</a>
      </div>
    </section>
  </main>

  <?php include 'components/footer.php'; ?>

  <script>
    // Highlight active tab
    document.querySelectorAll('.faq-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.faq-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
      });
    });

    // FAQ question accordion
    document.querySelectorAll('.faq-question').forEach(question => {
      question.addEventListener('click', () => {
        const isActive = question.classList.contains('active');
        document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));
        document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('show'));

        if (!isActive) {
          question.classList.add('active');
          question.nextElementSibling.classList.add('show');
        }
      });
    });

      window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`; // 20px extra buffer
  });
  </script>
</body>
</html>
