<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = "Thank you for your message! We'll get back to you within 24 hours.";
            $name = $email = $subject = $message = '';
        } catch (PDOException $e) {
            $error = "Error submitting your message. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us | JeevanSetu</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 2rem;
    }

    .contact-section h1 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      text-align: center;
    }

    .contact-section .subheading {
      font-size: 1.1rem;
      text-align: center;
      margin-bottom: 2rem;
    }

    .contact-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
    }

    .contact-form, .contact-info {
      flex: 1;
      min-width: 320px;
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
    }

    .btn {
      background-color: #007bff;
      color: #fff;
      padding: 0.7rem 1.4rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .alert {
      padding: 0.8rem 1rem;
      border-radius: 5px;
      margin-bottom: 1rem;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
    }

    .contact-info h2 {
      font-size: 1.6rem;
      margin-bottom: 1rem;
      border-bottom: 2px solid #007bff;
      padding-bottom: 0.5rem;
    }

    .info-item {
      margin-bottom: 1.5rem;
    }

    .info-item h3 {
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
      color: #007bff;
    }

    .info-item p {
      margin: 0.3rem 0;
    }

    .emergency-notice {
      background-color: #fff3cd;
      border-left: 5px solid #ffc107;
      padding: 1rem;
      border-radius: 5px;
    }

    .faq-preview {
      margin-top: 4rem;
      padding: 2rem;
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .faq-preview h2 {
      margin-bottom: 1rem;
    }

    .faq-preview ul {
      padding-left: 1.5rem;
    }

    @media screen and (max-width: 768px) {
      .contact-grid {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<?php include 'components/header.php'; ?>

<main class="container">
  <section class="contact-section">
    <h1>Contact JeevanSetu</h1>
    <p class="subheading">Have questions or feedback? We'd love to hear from you.</p>

    <div class="contact-grid">
      <div class="contact-form">
        <?php if ($success): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" value="<?= $name ?? '' ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= $email ?? '' ?>" required>
          </div>

          <div class="form-group">
            <label for="subject">Subject</label>
            <select id="subject" name="subject" required>
              <option value="">Select a subject</option>
              <option value="General Inquiry" <?= ($subject ?? '') === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
              <option value="Donor Registration" <?= ($subject ?? '') === 'Donor Registration' ? 'selected' : '' ?>>Donor Registration</option>
              <option value="Request Help" <?= ($subject ?? '') === 'Request Help' ? 'selected' : '' ?>>Request Help</option>
              <option value="Technical Support" <?= ($subject ?? '') === 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
              <option value="Partnership" <?= ($subject ?? '') === 'Partnership' ? 'selected' : '' ?>>Partnership</option>
              <option value="Other" <?= ($subject ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="message">Your Message</label>
            <textarea id="message" name="message" rows="5" required><?= $message ?? '' ?></textarea>
          </div>

          <button type="submit" class="btn">Send Message</button>
        </form>
      </div>

      <div class="contact-info">
        <h2>Other Ways to Reach Us</h2>

        <div class="info-item">
          <h3><i class="icon location"></i> Our Office</h3>
          <p>123 Health Street<br>Mumbai, Maharashtra 400001<br>India</p>
        </div>

        <div class="info-item">
          <h3><i class="icon phone"></i> Phone Support</h3>
          <p>Toll-Free: 1800-123-4567<br>International: +91 22 98765432</p>
          <p>Monday to Friday: 9AM to 6PM IST<br>Saturday: 10AM to 4PM IST</p>
        </div>

        <div class="info-item">
          <h3><i class="icon email"></i> Email</h3>
          <p>General: <a href="mailto:info@jeevansetu.org">info@jeevansetu.org</a></p>
          <p>Support: <a href="mailto:support@jeevansetu.org">support@jeevansetu.org</a></p>
          <p>Partnerships: <a href="mailto:partners@jeevansetu.org">partners@jeevansetu.org</a></p>
        </div>

        <div class="emergency-notice">
          <h3><i class="icon alert"></i> For Medical Emergencies</h3>
          <p>If you have an immediate medical emergency requiring blood or organ donation, please:</p>
          <ol>
            <li>Call your local emergency number</li>
            <li>Submit a request on our <a href="requester/request_form.php">Emergency Request Form</a></li>
            <li>We will prioritize your case immediately</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="faq-preview">
    <h2>Quick Answers</h2>
    <p>Visit our <a href="faq.php">FAQ page</a> for answers to common questions:</p>
    <ul>
      <li>Donor eligibility</li>
      <li>Registration process</li>
      <li>Requesting help</li>
      <li>Technical issues</li>
    </ul>
  </section>
</main>

<?php include 'components/footer.php'; ?>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    if (header) {
      document.body.style.paddingTop = `${header.offsetHeight + 20}px`;
    }
  });
</script>

</body>
</html>
