<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Weekly Calendar Grade</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
        }
        .grade-section {
            text-align: center;
            padding: 30px 0;
        }
        .grade-circle {
            display: inline-block;
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            font-size: 36px;
            font-weight: bold;
            color: white;
            margin-bottom: 15px;
        }
        .grade-a {
            background-color: #4caf50;
        }
        .grade-b {
            background-color: #8bc34a;
        }
        .grade-c {
            background-color: #ffc107;
        }
        .grade-d {
            background-color: #ff9800;
        }
        .grade-f {
            background-color: #f44336;
        }
        .section {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .section h2 {
            color: #4a90e2;
            font-size: 18px;
        }
        .section ul {
            padding-left: 20px;
        }
        .section li {
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Own My Calendar</div>
        </div>
        
        <div class="content">
            <h1>Hello {{ $user->name }},</h1>
            
            <p>Here's your weekly calendar grade for {{ date('F j', strtotime($weekStart)) }} - {{ date('F j, Y', strtotime($weekEnd)) }}.</p>
            
            <div class="grade-section">
                <div class="grade-circle {{ strtolower(substr($overallGrade, 0, 1)) === 'a' ? 'grade-a' : (strtolower(substr($overallGrade, 0, 1)) === 'b' ? 'grade-b' : (strtolower(substr($overallGrade, 0, 1)) === 'c' ? 'grade-c' : (strtolower(substr($overallGrade, 0, 1)) === 'd' ? 'grade-d' : 'grade-f'))) }}">
                    {{ $overallGrade }}
                </div>
                <h2>Overall Grade</h2>
            </div>
            
            <div class="section">
                <h2>Strengths</h2>
                <ul>
                    @foreach($strengths as $strength)
                        <li>{{ $strength }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div class="section">
                <h2>Areas for Improvement</h2>
                <ul>
                    @foreach($improvements as $improvement)
                        <li>{{ $improvement }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div class="section">
                <h2>Recommendations</h2>
                <ul>
                    @foreach($recommendations as $recommendation)
                        <li>{{ $recommendation }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ $appUrl }}/dashboard" class="button">View Full Details</a>
            </div>
        </div>
        
        <div class="footer">
            <p>You're receiving this email because you've enabled weekly grade notifications in your <a href="{{ $appUrl }}/settings">notification settings</a>.</p>
            <p>&copy; {{ date('Y') }} Own My Calendar. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
