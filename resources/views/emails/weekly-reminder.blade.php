<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time to Plan Your Week</title>
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
        .calendar-rule {
            background-color: #f5f7fa;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
            border-left: 3px solid #4a90e2;
        }
        .rule-letter {
            font-weight: bold;
            color: #4a90e2;
            margin-right: 5px;
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
            
            <p>It's time to plan your week! Taking a few minutes now to organize your calendar for the upcoming week ({{ date('F j', strtotime($weekStart)) }} - {{ date('F j, Y', strtotime($weekEnd)) }}) will help you stay on track with your goals and priorities.</p>
            
            <div class="section">
                <h2>Planning Tips</h2>
                <p>Remember these key calendar rules when planning your week:</p>
                
                @foreach($calendarRules as $letter => $rule)
                    <div class="calendar-rule">
                        <span class="rule-letter">{{ $letter }}.</span> {{ $rule }}
                    </div>
                @endforeach
                
                <p>Following these principles will help you create a more effective calendar that aligns with your goals and priorities.</p>
            </div>
            
            <div class="section">
                <h2>Steps to Plan Your Week</h2>
                <ol>
                    <li>Review your goals and priorities for the week</li>
                    <li>Block time for your non-negotiables first</li>
                    <li>Schedule focus time for important work</li>
                    <li>Add buffer time between meetings</li>
                    <li>Include time for self-care and breaks</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ $appUrl }}/calendar" class="button">Open My Calendar</a>
            </div>
        </div>
        
        <div class="footer">
            <p>You're receiving this email because you've enabled weekly planning reminders in your <a href="{{ $appUrl }}/settings">notification settings</a>.</p>
            <p>&copy; {{ date('Y') }} Own My Calendar. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
