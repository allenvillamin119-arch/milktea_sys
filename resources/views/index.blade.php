@extends('layouts.main')

@section('title', 'Welcome')
@section('page-title', 'Welcome')

@section('guest-content')
<style>
    /* Ensure guest welcome page is full-width and not pushed by authenticated sidebar */
    body { padding-left: 0 !important; }
    .welcome-wrap {
        min-height: 100vh;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:2rem 1rem;
        /* Milktea background image */
        background-image: url('{{ asset('images/milktea.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
    /* Dark overlay to improve readability */
    .welcome-wrap::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 0;
    }
    .welcome-card { position: relative; z-index: 1; }
    .welcome-card { width: 100%; max-width: 1100px; border-radius: 18px; overflow:hidden; box-shadow: 0 20px 50px rgba(15,23,42,0.18); display:grid; grid-template-columns: 1fr 420px; }
    .welcome-card { margin: 0 auto; }
    .welcome-visual { background: linear-gradient(180deg, rgba(250,248,246,1), rgba(244,241,238,1)); padding: 48px; display:flex; flex-direction:column; justify-content:center; align-items:flex-start; gap:18px; }
    .welcome-visual .logo-box { background: #fff; padding: 12px; border-radius: 12px; display:inline-flex; align-items:center; gap:12px; box-shadow: 0 8px 20px rgba(15,23,42,0.06); }
    /* Slideshow styles */
    /* Larger slideshow for visual impact; responsive below */
    .slideshow { width:300px; height:300px; border-radius:12px; overflow:hidden; position:relative; background:#fff; box-shadow: 0 8px 30px rgba(15,23,42,0.08); }
    .slideshow img.slide { position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .45s ease; }
    .slideshow img.slide.active { opacity:1; }
    .welcome-visual h1 { margin:0; font-size: 2rem; color: var(--dark-color); }
    .welcome-visual p.lead { margin:0; color: var(--muted); }

    .welcome-form { background: var(--dark-color); color: #fff; padding: 36px; display:flex; flex-direction:column; justify-content:center; }
    .welcome-form .brand { display:flex; gap:12px; align-items:center; margin-bottom: 20px; }
    /* Circular cropped logo — fill the circle exactly */
    .welcome-logo { width:72px; height:72px; object-fit:cover; border-radius:50%; background:transparent; padding:0; display:block; box-shadow: 0 6px 18px rgba(15,23,42,0.08); }
    .welcome-card .welcome-form .welcome-logo { width:56px; height:56px; }
    .welcome-form h3 { margin:0 0 6px 0; font-weight:700; }
    .welcome-form p.sub { margin:0 0 18px 0; color: rgba(255,255,255,0.7); }

    .form-control, .form-select { border-radius: 10px; }
    .btn-signin { background: #fff; color: var(--dark-color); border-radius: 999px; padding: 10px 26px; font-weight:700; }

    @media (max-width: 992px) {
        .welcome-card { grid-template-columns: 1fr; }
        .welcome-form { order: 2; }
        .slideshow { width:180px; height:180px; }
    }

    @media (max-width: 576px) {
        .slideshow { width:140px; height:140px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Simple slideshow that switches active image every 2s
        var slides = document.querySelectorAll('#welcomeSlideshow img.slide');
        if(!slides || slides.length <= 1) return;
        var idx = 0;
        setInterval(function(){
            slides[idx].classList.remove('active');
            idx = (idx + 1) % slides.length;
            slides[idx].classList.add('active');
        }, 2000);
    });
</script>

<div class="welcome-wrap">
    <div class="welcome-card">
        <div class="welcome-visual">
                <div class="logo-box">
                    <div class="slideshow" id="welcomeSlideshow" aria-hidden="false">
                        <img src="{{ asset('images/slide1.jpg') }}" class="slide active" alt="slide 1">
                        <img src="{{ asset('images/slide2.jpg') }}" class="slide" alt="slide 2">
                        <img src="{{ asset('images/slide3.jpg') }}" class="slide" alt="slide 3">
                    </div>
                    <div>
                        <strong style="font-size:1rem">{{ config('app.name') }}</strong>
                        <div style="font-size:0.85rem;color:var(--muted)">Business management simplified</div>
                    </div>
                </div>

            <h1>Manage sales, inventory, and staff with confidence</h1>
            <p class="lead">A clean, focused interface to help you run daily operations — POS, inventory, reports and staff management in one place.</p>

            <!-- brand summary removed per request -->
        </div>

        <div class="welcome-form">
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="logo" class="welcome-logo">
                <div>
                    <h3>Welcome Back</h3>
                    <p class="sub">Please login to your account</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <div class="form-check text-white">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <div class="d-grid">
                    <button class="btn btn-signin">Sign in</button>
                </div>
            </form>

            <!-- Removed social login and create-account links per request -->
        </div>
    </div>
</div>

@endsection