<div style="box-sizing:border-box;font-family:Roboto; display: block; text-align: center;">
    <div>
        <h2
            style='box-sizing:border-box;margin-top:8px!important;margin-bottom:0;font-size:24px;font-weight:400!important;line-height:1.25!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji"!important; text-align:center'>
            Notification from Product Recommendation App
            <br>
            Hello {{ $email }},
        </h2>
        <div
            style="box-sizing:border-box;font-family:Roboto,serif; display: inline-block; text-align: center; padding: 20px; border: 1px solid #fff; margin: 20px 0">
            <div style="box-sizing:border-box;font-size:16px!important;font-family:Roboto">
                The product recommendations refresh process for your shop {{ $shop_domain }} has been {{ $status === \App\Objects\Enums\JobRecommendationStatus::SUCCESS ? 'completed successfully' : 'failed' }}.
            </div>
            <div
                style='box-sizing:border-box;color:#24292e!important;display:block;background-color:#eaf5ff;border-radius:6px;padding:2px 6px;margin: 20px 0;font:300 48px "SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace'>
                {{ $status === \App\Objects\Enums\JobRecommendationStatus::SUCCESS ? 'Refresh completed successfully!' : 'An error occurred, please try again!' }}
            </div>
            <a href="https://admin.shopify.com/store/{{ $shop_domain }}/apps/product-recommendation-2212"
               rel="noopener noreferrer"
               style='background-color:#1f883d!important;box-sizing:border-box;color:#fff;text-decoration:none;display:inline-block;font-size:inherit;font-weight:500;line-height:1.5;white-space:nowrap;vertical-align:middle;border-radius:.5em;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji"!important;padding:.75em 1.5em;border:1px solid #1f883d'
               target="_blank">
                Go to App
            </a>
        </div>
        <div style="text-align: start;">
            <p style="font-size: 14px; color: #6c757d;">
                Best regards,<br>
                The Product Recommendation Team
            </p>
        </div>
    </div>
</div>
