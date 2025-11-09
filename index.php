<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techshilpo - Educational Technology Solutions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #e2e8f0;
            overflow-x: hidden;
            background: var(--darker);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        /* Grid Background */
        .grid-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background-color: var(--darker);
            background-image: 
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 100px 100px;
            animation: gridMove 20s linear infinite;
        }

        .grid-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.08) 0%, transparent 50%);
        }

        @keyframes gridMove {
            0% { background-position: 0 0; }
            100% { background-position: 100px 100px; }
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(2, 6, 23, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 0;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: #f8fafc;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .btn-login {
            color: #cbd5e1;
            border-color: rgba(203, 213, 225, 0.2);
        }

        .btn-login:hover {
            color: #fff;
            border-color: rgba(99, 102, 241, 0.5);
            background: rgba(99, 102, 241, 0.1);
        }

        .btn-register {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.6);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            padding-top: 120px;
            z-index: 1;
        }

        .hero-content {
            max-width: 1000px;
            animation: fadeInUp 1s ease-out;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 24px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 50px;
            color: #a5b4fc;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .hero h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 5rem;
            margin-bottom: 1rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.03em;
            color: #f8fafc;
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }

        .hero p {
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
            color: #94a3b8;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }

        .cta-group {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
        }

        .cta-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.4);
        }

        .cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 50px rgba(99, 102, 241, 0.6);
        }

        .cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px;
            background: transparent;
            border: 2px solid rgba(99, 102, 241, 0.3);
            color: #f8fafc;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
        }

        .cta-secondary:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateY(-3px);
        }

        .hero-stats {
            display: flex;
            gap: 4rem;
            justify-content: center;
            margin-top: 5rem;
            flex-wrap: wrap;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat h4 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #f8fafc;
            margin-bottom: 0.5rem;
        }

        .hero-stat p {
            font-size: 0.95rem;
            color: #64748b;
            margin: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Services Section */
        .services {
            padding: 120px 0;
            position: relative;
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-tag {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 50px;
            color: #a5b4fc;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            color: #f8fafc;
            letter-spacing: -0.02em;
        }

        .section-header p {
            font-size: 1.2rem;
            color: #94a3b8;
            max-width: 700px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #6366f1, transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .service-card:hover::before {
            transform: translateX(100%);
        }

        .service-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.4);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 25px 50px rgba(99, 102, 241, 0.2);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
        }

        .service-icon svg {
            width: 28px;
            height: 28px;
            stroke: #a5b4fc;
        }

        .service-card h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color: #f8fafc;
        }

        .service-card p {
            color: #94a3b8;
            line-height: 1.8;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .service-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: gap 0.3s ease;
        }

        .service-link:hover {
            gap: 12px;
        }

        /* Features Section */
        .features {
            padding: 120px 0;
            background: rgba(15, 23, 42, 0.3);
            position: relative;
            z-index: 1;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .feature-item {
            text-align: center;
            padding: 2rem;
        }

        .feature-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .feature-item h4 {
            font-size: 1.2rem;
            color: #f8fafc;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .feature-item p {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        /* Pricing Section */
        .pricing {
            padding: 120px 0;
            position: relative;
            z-index: 1;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .pricing-card {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
            padding: 3rem 2.5rem;
            border-radius: 20px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            transition: all 0.4s ease;
            position: relative;
        }

        .pricing-card.featured {
            border-color: rgba(99, 102, 241, 0.5);
            background: rgba(15, 23, 42, 0.8);
            transform: scale(1.05);
        }

        .pricing-card.featured::before {
            content: 'POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.4);
        }

        .pricing-card h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            color: #f8fafc;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .pricing-card .price {
            font-size: 3rem;
            font-weight: 900;
            color: #f8fafc;
            margin-bottom: 0.5rem;
        }

        .pricing-card .price span {
            font-size: 1.2rem;
            color: #64748b;
            font-weight: 500;
        }

        .pricing-card .period {
            color: #64748b;
            margin-bottom: 2rem;
            display: block;
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 2rem;
        }

        .pricing-features li {
            padding: 0.75rem 0;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pricing-features li::before {
            content: '✓';
            color: #6366f1;
            font-weight: 900;
            font-size: 1.2rem;
        }

        .pricing-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pricing-btn:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
        }

        .pricing-card.featured .pricing-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
        }

        .pricing-card.featured .pricing-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        /* Testimonials Section */
        .testimonials {
            padding: 120px 0;
            background: rgba(15, 23, 42, 0.3);
            position: relative;
            z-index: 1;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .testimonial-card {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            transition: all 0.4s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .testimonial-text {
            color: #cbd5e1;
            font-size: 1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .author-info h5 {
            color: #f8fafc;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .author-info p {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* CTA Section */
        .cta-section {
            padding: 120px 0;
            position: relative;
            z-index: 1;
        }

        .cta-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 30px;
            padding: 5rem 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .cta-box-content {
            position: relative;
            z-index: 1;
        }

        .cta-box h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            color: #f8fafc;
            margin-bottom: 1.5rem;
            font-weight: 900;
        }

        .cta-box p {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        footer {
            background: rgba(15, 23, 42, 0.5);
            border-top: 1px solid rgba(99, 102, 241, 0.1);
            padding: 5rem 0 2rem;
            position: relative;
            z-index: 1;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-family: 'Space Grotesk', sans-serif;
            margin-bottom: 1.5rem;
            color: #f8fafc;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .footer-section p {
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }

        .footer-links a:hover {
            color: #a5b4fc;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(99, 102, 241, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }

            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 3rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .hero-stats {
                gap: 2rem;
            }

            .section-header h2 {
                font-size: 2.5rem;
            }

            .services-grid,
            .features-grid,
            .pricing-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .cta-box h2 {
                font-size: 2rem;
            }

            .cta-group {
                flex-direction: column;
                width: 100%;
            }

            .cta-primary,
            .cta-secondary {
                width: 100%;
                justify-content: center;
            }

            .pricing-card.featured {
                transform: scale(1);
            }
        }

        /* Scroll Reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--darker);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
        }
    </style>
</head>
<body>
    <!-- Grid Background -->
    <div class="grid-background"></div>

    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <div class="logo"><img src="https://techshilpo.com/logo/techshilpo.png" width="80" alt="Techshilpo Logo"></div>
                <div class="nav-links">
                    <a href="#services">Services</a>
                    <a href="#features">Features</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#testimonials">Testimonials</a>
                </div>
                <div class="auth-buttons">
                    <a href="Admin/index.php" class="btn btn-login">Login</a>
                    <a href="Admin/register.php" class="btn btn-register">Get Started</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    Next-Generation EdTech Platform
                </div>
                <h1>Transform Education with <span class="gradient-text">Digital Excellence</span></h1>
                <p>Empowering educational institutions with cutting-edge technology solutions. Streamline operations, enhance learning experiences, and build the future of education.</p>
                <div class="cta-group">
                    <a href="#services" class="cta-primary">
                        Explore Solutions
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    <a href="#" class="cta-secondary">
                        Watch Demo
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <h4>500+</h4>
                        <p>Institutions</p>
                    </div>
                    <div class="hero-stat">
                        <h4>50K+</h4>
                        <p>Active Users</p>
                    </div>
                    <div class="hero-stat">
                        <h4>99.9%</h4>
                        <p>Uptime</p>
                    </div>
                    <div class="hero-stat">
                        <h4>24/7</h4>
                        <p>Support</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">Our Services</div>
                <h2>Comprehensive Solutions for Modern Education</h2>
                <p>Everything your institution needs to succeed in the digital age</p>
            </div>
            <div class="services-grid">
                <div class="service-card reveal">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <h3>Custom Websites</h3>
                    <p>Stunning, responsive websites designed specifically for educational institutions. Modern design with intuitive navigation and mobile-first approach.</p>
                    
                </div>
                <div class="service-card reveal">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>School Management System</h3>
                    <p>Comprehensive ERP solution for seamless management of students, staff, attendance, grades, and all institutional operations.</p>
                    
                </div>
                <div class="service-card reveal">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                    </div>
                    <h3>Mobile Applications</h3>
                    <p>Native iOS and Android apps connecting students, teachers, and parents with real-time updates and seamless communication.</p>
                    
                </div>
                <div class="service-card reveal">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
                        </svg>
                    </div>
                    <h3>Cloud Solutions</h3>
                    <p>Enterprise-grade cloud infrastructure ensuring 99.9% uptime, automatic backups, and secure data storage with global accessibility.</p>
                    
                </div>
                <div class="service-card reveal">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <h3>Analytics & Reporting</h3>
                    <p>Powerful data visualization and insights tools to track performance metrics, identify trends, and make data-driven decisions.</p>
                    
                </div>
                <div class="service-card reveal">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3>Security & Support</h3>
                    <p>Bank-level security with encryption, regular audits, and compliance. Round-the-clock technical support from our expert team.</p>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">Why Choose Us</div>
                <h2>Built for Educational Excellence</h2>
                <p>Industry-leading features that set us apart</p>
            </div>
            <div class="features-grid">
                <div class="feature-item reveal">
                    <div class="feature-number">01</div>
                    <h4>Intuitive Interface</h4>
                    <p>User-friendly design that requires minimal training</p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-number">02</div>
                    <h4>Scalable Solutions</h4>
                    <p>Grow from small classrooms to large universities</p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-number">03</div>
                    <h4>Custom Integration</h4>
                    <p>Seamlessly connect with existing systems</p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-number">04</div>
                    <h4>Real-time Updates</h4>
                    <p>Instant notifications and live data synchronization</p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-number">05</div>
                    <h4>Advanced Reporting</h4>
                    <p>Comprehensive analytics and customizable reports</p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-number">06</div>
                    <h4>24/7 Availability</h4>
                    <p>Access your data anytime, anywhere, on any device</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section 
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">Pricing Plans</div>
                <h2>Choose Your Perfect Plan</h2>
                <p>Flexible pricing options for institutions of all sizes</p>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card reveal">
                    <h3>Starter</h3>
                    <div class="price">৳15,000<span>/month</span></div>
                    <span class="period">Perfect for small schools</span>
                    <ul class="pricing-features">
                        <li>Up to 200 students</li>
                        <li>Basic website</li>
                        <li>Student management</li>
                        <li>Attendance tracking</li>
                        <li>Email support</li>
                        <li>Monthly reports</li>
                    </ul>
                    <a href="#" class="pricing-btn">Get Started</a>
                </div>
                <div class="pricing-card featured reveal">
                    <h3>Professional</h3>
                    <div class="price">৳35,000<span>/month</span></div>
                    <span class="period">Ideal for medium schools</span>
                    <ul class="pricing-features">
                        <li>Up to 1000 students</li>
                        <li>Premium website</li>
                        <li>Mobile application</li>
                        <li>Advanced analytics</li>
                        <li>Priority support</li>
                        <li>Cloud storage (100GB)</li>
                        <li>Custom integrations</li>
                    </ul>
                    <a href="#" class="pricing-btn">Get Started</a>
                </div>
                <div class="pricing-card reveal">
                    <h3>Enterprise</h3>
                    <div class="price">Custom</div>
                    <span class="period">For large institutions</span>
                    <ul class="pricing-features">
                        <li>Unlimited students</li>
                        <li>Custom website design</li>
                        <li>Native mobile apps</li>
                        <li>Dedicated server</li>
                        <li>24/7 phone support</li>
                        <li>Unlimited storage</li>
                        <li>Training & onboarding</li>
                        <li>SLA guarantee</li>
                    </ul>
                    <a href="#" class="pricing-btn">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>-->

    <!-- Testimonials Section 
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">Testimonials</div>
                <h2>Trusted by Educational Leaders</h2>
                <p>See what our clients have to say</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card reveal">
                    <div class="testimonial-text">
                        "Amar Campus transformed how we manage our institution. The system is intuitive, powerful, and our staff adapted to it within days. Highly recommended!"
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">MR</div>
                        <div class="author-info">
                            <h5>Dr. Mohammad Rahman</h5>
                            <p>Principal, Dhaka International School</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <div class="testimonial-text">
                        "The mobile app has revolutionized parent-teacher communication. Parents love getting real-time updates about their children's progress and attendance."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">FA</div>
                        <div class="author-info">
                            <h5>Fatima Ahmed</h5>
                            <p>Director, Chittagong Grammar School</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <div class="testimonial-text">
                        "Outstanding service and support! The analytics features help us make informed decisions. Our administrative workload has decreased by 60%."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">SK</div>
                        <div class="author-info">
                            <h5>Sanjida Khan</h5>
                            <p>Administrator, Sylhet College</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>-->

    <!-- CTA Section
    <section class="cta-section">
        <div class="container">
            <div class="cta-box reveal">
                <div class="cta-box-content">
                    <h2>Ready to Transform Your Institution?</h2>
                    <p>Join hundreds of schools and colleges already benefiting from our platform. Start your digital transformation today.</p>
                    <div class="cta-group">
                        <a href="Admin/register.php" class="cta-primary">
                            Start Free Trial
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                        <a href="#" class="cta-secondary">
                            Schedule Demo
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <img src="https://techshilpo.com/logo/techshilpo.png" width="100" alt="Techshilpo Logo">
                    <p>Leading provider of digital transformation solutions for educational institutions. Empowering the future of education through innovative technology.</p>
                    
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul class="footer-links">
                        <li><a href="#">Website Development</a></li>
                        <li><a href="#">School Management</a></li>
                        <li><a href="#">Mobile Applications</a></li>
                        <li><a href="#">Cloud Solutions</a></li>
                        <li><a href="#">Analytics Platform</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Press Kit</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul class="footer-links">
                        <li><a href="mailto:info@amarcampus.com">info@amarcampus.com</a></li>
                        <li><a href="tel:+880XXXXXXXXX">+880-XXX-XXXXXX</a></li>
                        <li>Dhaka, Bangladesh</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Techshilpo. All rights reserved. Empowering Education Through Technology.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Scroll reveal animation
        const revealElements = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            const windowHeight = window.innerHeight;
            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        };

        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();

        // Header scroll effect
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const header = document.querySelector('header');
            
            if (currentScroll > 100) {
                header.style.background = 'rgba(2, 6, 23, 0.95)';
                header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.5)';
            } else {
                header.style.background = 'rgba(2, 6, 23, 0.8)';
                header.style.boxShadow = 'none';
            }
            
            lastScroll = currentScroll;
        });

        // Mobile menu toggle (for future implementation)
        const menuToggle = document.querySelector('.menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                document.querySelector('.nav-links').classList.toggle('active');
            });
        }
    </script>
</body>
</html>