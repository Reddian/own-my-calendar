@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Chrome Extension</h1>

<div class="extension-container">
    <div class="extension-icon">
        <i class="fas fa-puzzle-piece"></i>
    </div>

    <h2 class="extension-title">Grade Your Calendar on the Go</h2>

    <p class="extension-description">
        Install our Chrome Extension to grade your calendar directly from Google Calendar.
        Get instant feedback and recommendations without leaving your calendar view.
    </p>

    <a href="#" class="btn btn-primary btn-lg mt-3">
        <i class="fab fa-chrome me-2"></i> Add to Chrome
    </a>

    <div class="extension-features">
        <div class="feature-item">
            <div class="feature-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h3 class="feature-title">One-Click Grading</h3>
            <p>Grade your calendar with a single click while viewing Google Calendar</p>
        </div>

        <div class="feature-item">
            <div class="feature-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="feature-title">Instant Feedback</h3>
            <p>Get immediate grades and recommendations for your calendar</p>
        </div>

        <div class="feature-item">
            <div class="feature-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <h3 class="feature-title">Seamless Sync</h3>
            <p>All grades are automatically synced with your Own My Calendar account</p>
        </div>
    </div>

    <div class="extension-steps mt-5">
        <h3>How It Works</h3>

        <div class="steps-container">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Install the Extension</h4>
                    <p>Add the Own My Calendar extension to your Chrome browser</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Open Google Calendar</h4>
                    <p>Navigate to your Google Calendar in Chrome</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Click the Extension Icon</h4>
                    <p>Find the Own My Calendar icon in your browser toolbar</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Grade Your Calendar</h4>
                    <p>Click "Grade This Week" to analyze your current calendar view</p>
                </div>
            </div>
        </div>
    </div>

    <div class="extension-screenshot mt-5">
        <img src="{{ asset('images/extension-screenshot.png') }}" alt="Chrome Extension Screenshot" class="img-fluid rounded shadow">
    </div>
</div>
@endsection

@section('styles')
<style>
    .extension-container {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .extension-icon {
        font-size: 80px;
        color: var(--primary-purple);
        margin-bottom: 30px;
    }

    .extension-title {
        font-size: 32px;
        margin-bottom: 20px;
    }

    .extension-description {
        font-size: 18px;
        color: #666;
        margin-bottom: 30px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .extension-features {
        display: flex;
        justify-content: space-around;
        margin: 50px 0;
        flex-wrap: wrap;
    }

    .feature-item {
        flex: 0 0 30%;
        text-align: center;
        margin-bottom: 30px;
    }

    .feature-icon {
        font-size: 40px;
        color: var(--primary-teal);
        margin-bottom: 15px;
    }

    .feature-title {
        font-weight: bold;
        margin-bottom: 10px;
    }

    .steps-container {
        max-width: 600px;
        margin: 30px auto;
    }

    .step-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
        text-align: left;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-purple);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .step-content h4 {
        margin: 0 0 5px 0;
    }

    .step-content p {
        margin: 0;
        color: #666;
    }

    .extension-screenshot {
        margin-top: 50px;
    }

    .extension-screenshot img {
        max-width: 100%;
        border: 1px solid #ddd;
    }

    @media (max-width: 768px) {
        .extension-features {
            flex-direction: column;
        }

        .feature-item {
            flex: 0 0 100%;
        }
    }
</style>
@endsection
