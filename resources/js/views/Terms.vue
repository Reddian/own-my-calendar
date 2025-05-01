<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="card">
          <div class="card-header">
            <h1 class="text-center">Terms of Service</h1>
          </div>
          <div class="card-body terms-content">
            <p class="last-updated">Last Updated: April 22, 2025</p>
            
            <h2>1. Introduction</h2>
            <p>Welcome to Own My Calendar ("we," "our," or "us"). By accessing or using our website, mobile application, and services (collectively, the "Services"), you agree to be bound by these Terms of Service ("Terms"). Please read these Terms carefully before using our Services.</p>
            
            <h2>2. Acceptance of Terms</h2>
            <p>By accessing or using our Services, you acknowledge that you have read, understood, and agree to be bound by these Terms. If you do not agree to these Terms, you must not access or use our Services.</p>
            
            <h2>3. Changes to Terms</h2>
            <p>We reserve the right to modify these Terms at any time. We will provide notice of any material changes by posting the updated Terms on our website or through other communications. Your continued use of the Services after such changes constitutes your acceptance of the new Terms.</p>
            
            <h2>4. Account Registration</h2>
            <p>To use certain features of our Services, you may need to create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to provide accurate and complete information when creating your account and to update your information to keep it accurate and current.</p>
            
            <h2>5. Google Calendar Integration</h2>
            <p>Our Services allow you to connect and integrate with Google Calendar. By connecting your Google Calendar, you authorize us to access, store, and process your calendar data in accordance with our Privacy Policy. You acknowledge that your use of Google Calendar is subject to Google's Terms of Service and Privacy Policy.</p>
            
            <h2>6. Calendar Grading</h2>
            <p>Our Services include calendar grading features that analyze your calendar data to provide insights and recommendations. You understand that these grades and recommendations are for informational purposes only and should not be considered professional advice.</p>
            
            <h2>7. Subscription and Payment</h2>
            <p>We offer both free and paid subscription plans. By subscribing to a paid plan, you agree to pay all fees in accordance with the pricing and payment terms presented to you. Subscription fees are non-refundable except as required by law or as explicitly stated in these Terms.</p>
            
            <h2>8. Free Trial</h2>
            <p>We may offer a free trial of our premium features. At the end of the trial period, your account will automatically be charged for the subscription unless you cancel before the trial ends.</p>
            
            <h2>9. Cancellation and Termination</h2>
            <p>You may cancel your subscription at any time through your account settings. Upon cancellation, your subscription will remain active until the end of your current billing period. We reserve the right to terminate or suspend your account and access to our Services at any time for violations of these Terms or for any other reason at our discretion.</p>
            
            <h2>10. Intellectual Property</h2>
            <p>All content, features, and functionality of our Services, including but not limited to text, graphics, logos, icons, images, audio clips, and software, are owned by us, our licensors, or other providers and are protected by copyright, trademark, and other intellectual property laws.</p>
            
            <h2>11. User Content</h2>
            <p>You retain ownership of any content you submit, post, or display on or through our Services ("User Content"). By submitting User Content, you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, modify, adapt, publish, translate, and distribute your User Content in connection with providing and improving our Services.</p>
            
            <h2>12. Prohibited Conduct</h2>
            <p>You agree not to:</p>
            <ul>
              <li>Use our Services in any way that violates any applicable law or regulation</li>
              <li>Impersonate any person or entity or falsely state or misrepresent your affiliation with a person or entity</li>
              <li>Engage in any conduct that restricts or inhibits anyone's use or enjoyment of our Services</li>
              <li>Attempt to gain unauthorized access to our Services, user accounts, or computer systems</li>
              <li>Use our Services for any unauthorized or illegal purpose</li>
            </ul>
            
            <h2>13. Disclaimer of Warranties</h2>
            <p>OUR SERVICES ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED. TO THE FULLEST EXTENT PERMITTED BY LAW, WE DISCLAIM ALL WARRANTIES, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p>
            
            <h2>14. Limitation of Liability</h2>
            <p>TO THE FULLEST EXTENT PERMITTED BY LAW, IN NO EVENT SHALL WE BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO LOSS OF PROFITS, DATA, OR USE, ARISING OUT OF OR IN CONNECTION WITH THESE TERMS OR YOUR USE OF OUR SERVICES, WHETHER BASED ON CONTRACT, TORT, NEGLIGENCE, STRICT LIABILITY, OR OTHERWISE.</p>
            
            <h2>15. Indemnification</h2>
            <p>You agree to indemnify, defend, and hold harmless us and our officers, directors, employees, agents, and affiliates from and against any and all claims, liabilities, damages, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising from or relating to your use of our Services or violation of these Terms.</p>
            
            <h2>16. Governing Law</h2>
            <p>These Terms shall be governed by and construed in accordance with the laws of the State of California, without regard to its conflict of law provisions. Any legal action or proceeding arising out of or relating to these Terms or your use of our Services shall be brought exclusively in the federal or state courts located in San Francisco County, California.</p>
            
            <h2>17. Severability</h2>
            <p>If any provision of these Terms is held to be invalid, illegal, or unenforceable, the remaining provisions shall continue in full force and effect.</p>
            
            <h2>18. Entire Agreement</h2>
            <p>These Terms, together with our Privacy Policy, constitute the entire agreement between you and us regarding your use of our Services and supersede all prior and contemporaneous agreements, proposals, or representations, written or oral, concerning the subject matter of these Terms.</p>
            
            <h2>19. Contact Information</h2>
            <p>If you have any questions about these Terms, please contact us at:</p>
            <p>AI Momentum Lab<br>
            support@ownmycalendar.com<br>
            123 Calendar Street<br>
            San Francisco, CA 94105</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// No specific script logic needed for this static page
</script>

<style scoped>
    /* Styles copied from terms.blade.php */
    .terms-content {
        padding: 20px;
    }
    
    .terms-content h1 {
        font-size: 2.2rem;
        margin-bottom: 20px;
    }
    
    .terms-content h2 {
        font-size: 1.5rem;
        margin-top: 30px;
        margin-bottom: 15px;
        color: var(--primary-purple, #6a1b9a); /* Added fallback color */
    }
    
    .terms-content p, .terms-content ul {
        margin-bottom: 15px;
        line-height: 1.6;
    }
    
    .terms-content ul {
        padding-left: 20px;
    }
    
    .terms-content .last-updated {
        font-style: italic;
        color: #666;
        margin-bottom: 30px;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .card-header {
        background: linear-gradient(to right, var(--primary-purple, #6a1b9a), var(--primary-teal, #00796b)); /* Added fallback colors */
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
    }
</style>

