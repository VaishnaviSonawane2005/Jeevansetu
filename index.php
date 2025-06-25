<?php
session_start();
require_once 'includes/db_connect.php';

/* ---------- dynamic counts ---------- */
$stats = [];
foreach ([
    'donors'   => "SELECT COUNT(*) FROM donors WHERE status = 1",
    'requests' => "SELECT COUNT(*) FROM requests WHERE status = 'completed'",
    'cities'   => "SELECT COUNT(DISTINCT city) FROM donors WHERE city IS NOT NULL"
] as $key => $sql) {
    $stats[$key] = $pdo->query($sql)->fetchColumn();
}

/* ---------- recent success stories ---------- */
$success_stories = $pdo->query(
    "SELECT r.patient_name, r.hospital_city, u.name AS donor_name
     FROM requests r
     JOIN request_matches rm ON r.id = rm.request_id
     JOIN users u ON rm.donor_id = u.id
     WHERE r.status = 'completed'
     ORDER BY r.required_date DESC
     LIMIT 3"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>JeevanSetu – National Blood & Organ Donation Network</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />

    <style>
     
        a{text-decoration:none}
        img{max-width:100%;display:block}

        .container{max-width:1200px;padding:1rem;margin:auto}

        /* ---------- buttons ---------- */
        .btn{display:inline-block;padding:.75rem 1.75rem;border:2px solid transparent;border-radius:50px;font-weight:600;transition:.2s}
        .btn-primary{background:var(--clr-primary);color:#fff}
        .btn-outline-light{background:transparent;border-color:var(--clr-primary);color:var(--clr-primary)}
        .btn:hover{transform:translateY(-3px);box-shadow:var(--shadow-soft)}
        .btn-pulse{position:relative;isolation:isolate}
        .btn-pulse::after{content:'';position:absolute;inset:0;border-radius:inherit;border:2px solid #fff;animation:pulse 2s infinite ease-out;z-index:-1}
        @keyframes pulse{0%{opacity:1;transform:scale(1)}70%{opacity:0;transform:scale(1.5)}100%{opacity:0}}

        /* ---------- hero ---------- */
        .hero{display:flex;align-items:center;justify-content:space-between;padding-block:1rem;gap:2rem}
        .hero-content{flex:1 1 480px;min-width:280px;animation:fadeInUp .8s ease-out both}
        .hero-title{font-size:clamp(2.25rem,5vw,3.5rem);color:var(--clr-secondary);font-weight:700;margin-bottom:1rem;line-height:1.2}
        .hero-subtitle{font-size:clamp(1.125rem,2.5vw,1.5rem);margin-bottom:2rem;color:var(--clr-dark)}
        .hero-buttons{display:flex;flex-wrap:wrap;gap:1rem}
        .hero-image{flex:1 1 420px;min-width:260px;text-align:center;animation:float 4s ease-in-out infinite}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

        /* ---------- full-width breakout ---------- */
        .full-width {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    padding-inline: clamp(1rem, 5vw, 3rem); /* 👈 smart responsive spacing */
}


        /* ---------- stats blocks ---------- */
        .stats,.stats-section{display:flex;flex-wrap:wrap;gap:1.5rem;justify-content:center;align-items:stretch;margin-block:3rem}
        .stat-item,.stat-card{flex:1 0 200px;text-align:center;background:#fff;border-radius:var(--radius);padding:2rem 1rem;box-shadow:var(--shadow-soft);display:flex;flex-direction:column;justify-content:center}
        .stat-item h2,.stat-number{font-size:2.5rem;color:var(--clr-primary);font-weight:700}
        .stat-item p,.stat-label{margin-top:.25rem;font-size:1rem;font-weight:600;color:var(--clr-secondary)}

        /* ---------- how it works ---------- */
        .how-it-works{background:var(--clr-secondary);color:#fff;border-radius:var(--radius);padding:4rem 1rem;text-align:center;margin-bottom:3rem}
        .how-it-works h2{font-size:2rem;margin-bottom:2rem}
        .steps{display:flex;flex-wrap:wrap;gap:2rem;justify-content:center}
        .step{position:relative;flex:1 0 220px;background:rgba(255,255,255,.06);border-radius:var(--radius);padding:2.5rem 1.25rem 1.5rem;backdrop-filter:blur(2px)}
        .step-number{width:42px;height:42px;line-height:42px;border-radius:50%;background:var(--clr-primary);color:#fff;font-weight:700;position:absolute;top:-21px;left:calc(50% - 21px)}

        /* ---------- success stories ---------- */
        .success-stories{margin-block:3rem}
        .success-stories h2{text-align:center;font-size:2rem;margin-bottom:2rem;color:var(--clr-secondary)}
        .stories{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem}
        .story{background:#fff;border-radius:var(--radius);padding:1.5rem;box-shadow:var(--shadow-soft)}
        .patient{text-align:right;font-weight:600;color:var(--clr-primary)}

        /* ---------- call-to-action ---------- */
        .call-to-action{background:var(--clr-primary);color:#fff;border-radius:var(--radius);text-align:center;padding:4rem 1rem 4rem 1rem;margin-block:2rem}
        .call-to-action h2{font-size:2rem;margin-bottom:1rem}
        .call-to-action p{font-size:1.125rem;margin-bottom:2rem}
        .call-to-action .btn{background:#fff;color:var(--clr-primary);border:none}

        /* ---------- misc ---------- */
        @keyframes fadeInUp{0%{opacity:0;transform:translateY(30px)}100%{opacity:1;transform:translateY(0)}}
        @media(max-width:768px){
            .hero{flex-direction:column-reverse;text-align:center}
            .hero-image{margin-bottom:2rem}
            .stats,.stats-section{flex-direction:column;align-items:center}
        }
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<!-- HERO – now edge-to-edge -->
<section class="hero full-width"><!-- NEW -->
    <div class="hero-content">
        <h1 class="hero-title">Connecting Donors<br>With Those In Need</h1>
        <p class="hero-subtitle">India's most trusted blood & organ donation network</p>
        <div class="hero-buttons">
            <a href="auth/register.php?role=donor" class="btn btn-primary btn-pulse">Become a Donor</a>
            <a href="auth/register.php?role=requester" class="btn btn-outline-light">Request Help</a>
        </div>
    </div>
    <div class="hero-image">
        <img src="assets/hero-illustration.png" alt="Medical illustration">
    </div>
</section>

<!-- FULL-WIDTH dynamic stats -->
<section class="stats full-width">
    <div class="stat-item"><h2><?= number_format($stats['donors']); ?></h2><p>Active Donors</p></div>
    <div class="stat-item"><h2><?= number_format($stats['requests']); ?></h2><p>Lives Saved</p></div>
    <div class="stat-item"><h2><?= number_format($stats['cities']); ?></h2><p>Cities Covered</p></div>
</section>

<!-- FULL-WIDTH animated static stats -->
<section class="stats-section full-width">
    <div class="stat-card"><div class="stat-number" data-count="12500">0</div><div class="stat-label">Lives Saved</div></div>
    <div class="stat-card"><div class="stat-number" data-count="8500">0</div><div class="stat-label">Active Donors</div></div>
    <div class="stat-card"><div class="stat-number" data-count="120">0</div><div class="stat-label">Cities Covered</div></div>
</section>

<main class="container">

    <!-- HOW IT WORKS -->
    <section class="how-it-works">
        <h2>How JeevanSetu Works</h2>
        <div class="steps">
            <div class="step"><div class="step-number">1</div><h3>Register</h3><p>Sign up as a donor or create a request for blood/organ</p></div>
            <div class="step"><div class="step-number">2</div><h3>Match</h3><p>Our system finds the closest matching donor</p></div>
            <div class="step"><div class="step-number">3</div><h3>Connect</h3><p>Donor and recipient are connected to save a life</p></div>
        </div>
    </section>

    <!-- SUCCESS STORIES -->
    <?php if ($success_stories): ?>
    <section class="success-stories">
        <h2>Recent Success Stories</h2>
        <div class="stories">
            <?php foreach ($success_stories as $story): ?>
            <div class="story">
                <p>"Thanks to <?= htmlspecialchars($story['donor_name']); ?> who donated blood and saved my <?= rand(5,15); ?>-year-old relative in <?= htmlspecialchars($story['hospital_city']); ?>."</p>
                <div class="patient">- <?= htmlspecialchars($story['patient_name']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<!-- FULL-WIDTH CTA -->
<section class="call-to-action full-width">
    <h2>Ready to Make a Difference?</h2>
    <p>Join our network today and become a lifesaver in your community.</p>
    <a href="auth/register.php" class="btn">Register Now</a>
</section>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    /* animate counters */
    document.querySelectorAll('.stat-number').forEach(el=>{
        const target=parseInt(el.dataset.count,10);
        let current=0;
        const inc=Math.max(1,Math.ceil(target/120));
        const tick=()=>{
            current+=inc;
            if(current>=target){current=target;el.textContent=current.toLocaleString();}
            else{el.textContent=current.toLocaleString();requestAnimationFrame(tick);}
        };
        requestAnimationFrame(tick);
    });

    /* header offset (if header is fixed) */
    const header=document.querySelector('.header');
    if(header){
        document.body.style.paddingTop=`${header.offsetHeight+20}px`;
    }
});

  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    document.body.style.paddingTop = `${headerHeight + 20}px`; // 20px extra buffer
  });
</script>
</body>
</html>
