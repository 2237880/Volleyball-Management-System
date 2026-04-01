<?php
declare(strict_types=1);

$loggedIn = isset($_SESSION['user']);
?>
<style>
    .landing-hero {
        background: linear-gradient(135deg, #0f2744 0%, #1a4d7a 45%, #0d6efd 100%);
        color: #fff;
        border-radius: 1rem;
        padding: 3rem 2.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1rem 2.5rem rgba(15, 39, 68, 0.35);
    }
    .landing-hero::after {
        content: "";
        position: absolute;
        right: -8%;
        top: -20%;
        width: 45%;
        height: 140%;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .landing-hero h1 {
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .landing-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        margin-bottom: 1rem;
    }
    .landing-feature-card {
        border: none;
        border-radius: 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .landing-feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.08) !important;
    }
    .landing-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 1rem;
    }
    .landing-cta-strip {
        background: #fff;
        border-radius: 0.75rem;
        border: 1px solid rgba(0,0,0,0.06);
        padding: 1.5rem 2rem;
    }
</style>

<div class="landing-hero">
    <div class="row align-items-center position-relative" style="z-index: 1;">
        <div class="col-lg-7">
            <span class="landing-badge">Tournament management</span>
            <h1 class="display-5 mb-3">Volleyball Management System</h1>
            <p class="lead opacity-90 mb-4 mb-lg-5">
                Run teams, fixtures, live scores, and role-based dashboards in one place—built for demos, coursework, and real club use.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <?php if (!$loggedIn): ?>
                    <a class="btn btn-light btn-lg px-4 fw-semibold" href="./index.php?r=login">Sign in</a>
                    <a class="btn btn-outline-light btn-lg px-4" href="./index.php?r=register-player">Create account</a>
                <?php else: ?>
                    <a class="btn btn-light btn-lg px-4 fw-semibold" href="./index.php?r=dashboard">Open dashboard</a>
                    <a class="btn btn-outline-light btn-lg px-4" href="./index.php?r=teams">My teams</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0 text-lg-end">
            <div class="d-inline-block text-start bg-white bg-opacity-10 rounded-3 p-4 border border-white border-opacity-25">
                <div class="small text-white-50 text-uppercase fw-semibold mb-2">Quick access</div>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><a class="text-white text-decoration-none" href="./index.php?r=login">Login</a></li>
                    <li class="mb-2"><a class="text-white text-decoration-none" href="./index.php?r=register-coach">Coach registration</a></li>
                    <li class="mb-0"><a class="text-white text-decoration-none" href="./index.php?r=register-player">Player registration</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card landing-feature-card shadow-sm">
            <div class="card-body p-4">
                <div class="landing-icon bg-primary bg-opacity-10 text-primary">👥</div>
                <h2 class="h5 fw-semibold">Teams &amp; rosters</h2>
                <p class="text-muted small mb-0">Create teams, assign coaches, add players, and set captains with full CRUD.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card landing-feature-card shadow-sm">
            <div class="card-body p-4">
                <div class="landing-icon bg-success bg-opacity-10 text-success">📅</div>
                <h2 class="h5 fw-semibold">Match scheduling</h2>
                <p class="text-muted small mb-0">Fixtures with date and time, home vs away teams, and upcoming match views.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card landing-feature-card shadow-sm">
            <div class="card-body p-4">
                <div class="landing-icon bg-warning bg-opacity-10 text-warning">🏆</div>
                <h2 class="h5 fw-semibold">Scores &amp; history</h2>
                <p class="text-muted small mb-0">Enter results, automatic winner logic, and match history by role.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card landing-feature-card shadow-sm">
            <div class="card-body p-4">
                <div class="landing-icon bg-info bg-opacity-10 text-info">📊</div>
                <h2 class="h5 fw-semibold">Stats dashboard</h2>
                <p class="text-muted small mb-0">AJAX-powered player stats, team ranking, and leaderboard—no full page reload.</p>
            </div>
        </div>
    </div>
</div>

<div class="landing-cta-strip shadow-sm mb-4">
    <div class="row align-items-center gy-3">
        <div class="col-md">
            <strong class="d-block mb-1">Ready to explore?</strong>
            <span class="text-muted small">Admin, coach, and player roles each see tailored menus and data.</span>
        </div>
        <div class="col-md-auto">
            <?php if (!$loggedIn): ?>
                <a class="btn btn-primary" href="./index.php?r=login">Go to login</a>
            <?php else: ?>
                <a class="btn btn-primary" href="./index.php?r=matches">View matches</a>
            <?php endif; ?>
        </div>
    </div>
</div>
