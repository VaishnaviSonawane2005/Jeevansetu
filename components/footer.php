<?php
echo '
<footer class="main-footer">
  <style>
    .main-footer {
      background-color: #0c1e35;
      color: #e0e0e0;
      font-family: "Segoe UI", sans-serif;
      padding: 40px 0 20px;
    }
    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 30px;
    }
    .footer-section h3 {
      color: #ffc107;
      margin-bottom: 12px;
      font-size: 16px;
    }
    .footer-section p, 
    .footer-section a {
      color: #f0f0f0;
      font-size: 14px;
      margin: 5px 0;
      text-decoration: none;
    }
    .footer-section a:hover {
      color: #ff5722;
      transition: 0.3s;
    }
    .footer-section ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .footer-section ul li {
      margin-bottom: 6px;
    }

    /* Social icons */
    .social-links {
      display: flex;
      gap: 12px;
      margin-top: 10px;
    }
    .social-icon svg {
      width: 28px;
      height: 28px;
      fill: #ffffff;
      transition: fill 0.3s ease;
    }
    .social-icon:hover svg {
      fill: #ffc107;
    }

    .footer-logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .footer-logo img {
      width: 36px;
      height: 36px;
    }
    .footer-logo span {
      font-size: 18px;
      color: #ffc107;
      font-weight: 600;
    }

    .footer-bottom {
      text-align: center;
      margin-top: 30px;
      font-size: 13px;
      color: #bbbbbb;
      border-top: 1px solid #223a59;
      padding-top: 14px;
    }

    @media (max-width: 768px) {
      .footer-container {
        grid-template-columns: 1fr 1fr;
      }
    }
    @media (max-width: 500px) {
      .footer-container {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="footer-container">
    <div class="footer-section">
      <div class="footer-logo">
        <span>JeevanSetu</span>
      </div>
      <p>Connecting blood and organ donors with those in need across India.</p>
      <div class="social-links">
        <a class="social-icon" href="#" target="_blank" title="Facebook">
          <svg viewBox="0 0 24 24">
            <path d="M22 12.07C22 6.51 17.52 2 12 2S2 6.51 2 12.07c0 4.84 3.44 8.84 7.94 9.76v-6.91H7.1v-2.85h2.84V9.75c0-2.8 1.67-4.34 4.22-4.34 1.22 0 2.5.22 2.5.22v2.75h-1.41c-1.39 0-1.82.87-1.82 1.76v2.1h3.1l-.5 2.85h-2.6v6.91c4.5-.92 7.94-4.92 7.94-9.76z"/>
          </svg>
        </a>
        <a class="social-icon" href="#" target="_blank" title="Twitter">
          <svg viewBox="0 0 24 24">
            <path d="M22.46 6c-.77.34-1.6.57-2.46.67a4.27 4.27 0 0 0 1.88-2.35 8.47 8.47 0 0 1-2.7 1.03 4.24 4.24 0 0 0-7.34 3.87 12.04 12.04 0 0 1-8.75-4.43 4.2 4.2 0 0 0 1.31 5.66 4.2 4.2 0 0 1-1.92-.53v.05a4.24 4.24 0 0 0 3.4 4.15 4.26 4.26 0 0 1-1.91.07 4.26 4.26 0 0 0 3.97 2.95A8.5 8.5 0 0 1 2 19.55 12.02 12.02 0 0 0 8.29 21c7.55 0 11.68-6.26 11.68-11.68 0-.18 0-.35-.01-.53A8.32 8.32 0 0 0 22.46 6z"/>
          </svg>
        </a>
        <a class="social-icon" href="#" target="_blank" title="Instagram">
          <svg viewBox="0 0 24 24">
            <path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h10zm-5 3.5A4.5 4.5 0 0 0 7.5 12 4.5 4.5 0 0 0 12 16.5 4.5 4.5 0 0 0 16.5 12 4.5 4.5 0 0 0 12 7.5zm0 2A2.5 2.5 0 0 1 14.5 12 2.5 2.5 0 0 1 12 14.5 2.5 2.5 0 0 1 9.5 12 2.5 2.5 0 0 1 12 9.5zM17.5 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
          </svg>
        </a>
      </div>
    </div>

    <div class="footer-section">
      <h3>Quick Links</h3>
      <ul>
        <li><a href="/index.php">Home</a></li>
        <li><a href="/about.php">About Us</a></li>
        <li><a href="/faq.php">FAQs</a></li>
        <li><a href="/contact.php">Contact</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h3>Important</h3>
      <ul>
        <li><a href="/privacy.php">Privacy Policy</a></li>
        <li><a href="/terms.php">Terms of Service</a></li>
        <li><a href="/donor/register_donation.php">Become a Donor</a></li>
        <li><a href="/requester/request_form.php">Request Help</a></li>
      </ul>
    </div>

    <div class="footer-section">
      <h3>Contact Us</h3>
      <p>📍 123 Health Street, Mumbai, India</p>
      <p>📞 +91 9876543210</p>
      <p>✉️ contact@jeevansetu.org</p>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; ' . date('Y') . ' JeevanSetu – National Blood & Organ Donation Network. All rights reserved.</p>
  </div>
</footer>
';
?>
