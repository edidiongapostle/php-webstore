<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
            <h1 style="color: white; margin: 0; font-size: 28px;">{{site_name}}</h1>
        </div>
        <div style="background: #ffffff; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-top: 0;">Order Payment Verification Failed</h2>
            <p>We're sorry to inform you that your order <strong>{{order_reference}}</strong> could not be verified.</p>

            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #f5c6cb;">
                <h4 style="margin-top: 0; color: #721c24;">What This Means:</h4>
                <p style="color: #721c24; margin-bottom: 0;">The payment proof you submitted could not be verified. This could be due to:</p>
                <ul style="margin: 10px 0; padding-left: 20px; color: #721c24;">
                    <li>Unclear or incomplete payment screenshot</li>
                    <li>Transaction reference could not be matched</li>
                    <li>Payment amount does not match order total</li>
                </ul>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #333;">What You Can Do:</h4>
                <ol style="margin: 10px 0; padding-left: 20px; color: #333;">
                    <li>Contact our support team with your payment details</li>
                    <li>Provide a clearer payment screenshot</li>
                    <li>Verify the transaction reference is correct</li>
                </ol>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #333;">Order Details:</h4>
                <p><strong>Order Reference:</strong> {{order_reference}}</p>
                <p><strong>Total Amount:</strong> {{total_amount}}</p>
            </div>

            <p style="color: #666; font-size: 14px;">If you believe this is an error, please contact our support team immediately.</p>

            <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

            <p style="text-align: center; color: #999; font-size: 12px;">
                &copy; {{current_year}} {{site_name}}. All rights reserved.<br>
                This is an automated email, please do not reply.
            </p>
        </div>
    </div>
</body>
</html>
