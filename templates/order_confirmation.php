<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
            <h1 style="color: white; margin: 0; font-size: 28px;">{{site_name}}</h1>
        </div>
        <div style="background: #ffffff; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-top: 0;">Order Confirmation</h2>
            <p>Thank you for your order! Your order has been successfully placed and is now awaiting payment verification.</p>

            <div style="background: #f8f9fa; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0; color: #667eea;">Order Details</h3>
                <p><strong>Order Reference:</strong> {{order_reference}}</p>
                <p><strong>Total Amount:</strong> {{total_amount}}</p>
                <p><strong>Payment Method:</strong> {{payment_method}}</p>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #ffeeba;">
                <h4 style="margin-top: 0; color: #856404;">Next Steps:</h4>
                <ol style="margin: 10px 0; padding-left: 20px; color: #856404;">
                    <li>Complete your payment using the provided instructions</li>
                    <li>Submit your payment details (transaction reference and screenshot)</li>
                    <li>Wait for our team to verify your payment</li>
                    <li>Once approved, you'll receive a download link</li>
                </ol>
            </div>

            <p style="color: #666; font-size: 14px;">If you have any questions, please contact our support team.</p>

            <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

            <p style="text-align: center; color: #999; font-size: 12px;">
                &copy; {{current_year}} {{site_name}}. All rights reserved.<br>
                This is an automated email, please do not reply.
            </p>
        </div>
    </div>
</body>
</html>
