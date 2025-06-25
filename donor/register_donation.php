<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

/* -------- check if donor profile exists -------- */
$stmt = $pdo->prepare("SELECT * FROM donors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$donor = $stmt->fetch();

if (!$donor) {
    header("Location: update_profile.php");
    exit();
}

/* -------- handle update -------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_group    = sanitize_input($_POST['blood_group']);
    $organs         = isset($_POST['organs']) ? implode(',', $_POST['organs']) : '';
    $city           = sanitize_input($_POST['city']);
    $contact_number = sanitize_input($_POST['contact_number']);
    $availability   = isset($_POST['availability']) ? 1 : 0;

    try {
        $pdo->prepare(
          "UPDATE donors 
             SET blood_group = ?, organs = ?, city = ?, contact_number = ?, status = ? 
           WHERE user_id = ?"
        )->execute([$blood_group,$organs,$city,$contact_number,$availability,$_SESSION['user_id']]);

        $success = "Donor information updated successfully!";
        // refresh donor info
        $stmt->execute([$_SESSION['user_id']]);
        $donor = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error updating donor information: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Register as Donor | JeevanSetu</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  :root{
    --primary:#e63946;
    --primary-dark:#c92d3b;
    --bg-1:#ff9e7c;
    --bg-2:#ffc55e;
  }

  /* gradient only for this section */
  .donor-section{
    padding:3rem 1rem 4rem;
    background:linear-gradient(-45deg,
               var(--primary),var(--bg-1),var(--bg-2),var(--primary-dark));
    background-size:400% 400%;
    animation:bgShift 12s ease infinite;
    display:flex;justify-content:center;
  }
  @keyframes bgShift{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}

  .donor-card{
    background:#fff;width:100%;max-width:500px;border-radius:14px;
    box-shadow:0 12px 28px rgba(0,0,0,.15);
    padding:2.5rem 2rem 2rem;animation:fadeSlide .5s ease both;
  }
  @keyframes fadeSlide{from{opacity:0;transform:translateY(20px)}}

  .donor-card h1{margin:0 0 1.8rem;text-align:center;
                 font-size:1.8rem;color:var(--primary)}

  .form-group{margin-bottom:1.2rem}
  .form-group label{display:block;font-weight:600;margin-bottom:.35rem}
  .form-group input,
  .form-group select,
  .checkbox-group{width:100%}
  .form-group input,
  .form-group select{
    padding:.65rem 1rem;border:1px solid #ccc;border-radius:9px;
    font-size:1rem;transition:border-color .25s ease,box-shadow .25s ease;
  }
  .form-group input:focus,
  .form-group select:focus{
    outline:none;border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(230,57,70,.3)
  }

  .checkbox-group{display:flex;flex-wrap:wrap;gap:.5rem}
  .checkbox-group label{
    background:#f5f5f5;border:1px solid #ccc;border-radius:8px;
    padding:.45rem .8rem;font-size:.9rem;cursor:pointer;
    transition:background .2s ease;
  }
  .checkbox-group input{margin-right:.3rem}
  .checkbox-group label:hover{background:#eaeaea}

  .btn-primary{
    width:100%;padding:.8rem 1rem;margin-top:1rem;
    background:var(--primary);color:#fff;border:none;border-radius:9px;
    font-weight:700;font-size:1.05rem;cursor:pointer;
    transition:background .25s ease,transform .15s ease;
  }
  .btn-primary:hover{background:var(--primary-dark);transform:translateY(-2px)}

  .alert{padding:.9rem 1rem;border-radius:9px;margin-bottom:1.4rem;font-weight:600}
  .alert-danger{background:#ffe6e6;color:#b00020}
  .alert-success{background:#e6ffe8;color:#006d1b}
</style>
</head>
<body>

<?php include '../components/header.php'; ?>

<section class="donor-section">
  <div class="donor-card">
    <h1>Register as a Donor</h1>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?= $error; ?></div>
    <?php elseif(isset($success)): ?>
      <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <form method="post">
      <!-- Basic -->
      <h2 style="margin-bottom:.8rem;color:var(--primary)">Basic Information</h2>

      <div class="form-group">
        <label for="blood_group">Blood Group</label>
        <select id="blood_group" name="blood_group" required>
          <option value="">Select Blood Group</option>
          <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
            <option value="<?= $bg ?>"
              <?= ($donor['blood_group']??'')===$bg?'selected':'' ?>><?= $bg ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Organs Willing to Donate</label>
        <div class="checkbox-group">
          <?php
            $opts=['kidney'=>'Kidney','liver'=>'Liver (partial)','pancreas'=>'Pancreas',
                   'heart'=>'Heart','lungs'=>'Lungs','eyes'=>'Eyes'];
            foreach($opts as $val=>$label):
            $checked = isset($donor['organs']) && strpos($donor['organs'],$val)!==false;
          ?>
          <label>
            <input type="checkbox" name="organs[]" value="<?= $val ?>"
                   <?= $checked?'checked':'' ?>> <?= $label ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Contact -->
      <h2 style="margin:1.6rem 0 .8rem;color:var(--primary)">Contact Information</h2>

      <div class="form-group">
        <label for="city">City</label>
        <input id="city" name="city" type="text" required
               value="<?= $donor['city']??'' ?>">
      </div>

      <div class="form-group">
        <label for="contact_number">Contact Number</label>
        <input id="contact_number" name="contact_number" type="tel" required
               value="<?= $donor['contact_number']??'' ?>">
      </div>

      <!-- Availability -->
      <h2 style="margin:1.6rem 0 .8rem;color:var(--primary)">Availability</h2>

      <div class="form-group">
        <label style="display:flex;align-items:center;font-weight:600">
          <input type="checkbox" name="availability"
                 <?= ($donor['status']??0)==1?'checked':'' ?>
                 style="margin-right:.5rem">
          I'm currently available to donate
        </label>
        <p style="font-size:.85rem;color:#666;margin-top:.25rem">
          When checked, your profile becomes visible to requesters.
        </p>
      </div>

      <button type="submit" class="btn-primary">Save Donor Profile</button>
    </form>
  </div>
</section>

<?php include '../components/footer.php'; ?>
<script>
      window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`; // 20px extra buffer
  });
</script>

</body>
</html>
