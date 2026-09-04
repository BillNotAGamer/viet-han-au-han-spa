@if($mode === 'gtm' && $gtmId)
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
<!-- End Google Tag Manager -->
@if($conversion)
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
    event: 'booking_request_submitted',
    locale: {{ Js::from($conversion['locale']) }},
    service_id: {{ Js::from($conversion['service_id']) }}
});
</script>
@endif
@elseif($mode === 'direct')
@if($ga4Id)
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{{ $ga4Id }}');
</script>
@endif
@if($metaPixelId)
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $metaPixelId }}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
@endif
@if($conversion)
@if($ga4Id)
<script>
if (typeof gtag === 'function') {
    gtag('event', 'generate_lead', {
        locale: {{ Js::from($conversion['locale']) }},
        service_id: {{ Js::from($conversion['service_id']) }}
    });
}
</script>
@endif
@if($metaPixelId)
<script>
if (typeof fbq === 'function') {
    fbq('track', 'Lead', {
        locale: {{ Js::from($conversion['locale']) }},
        service_id: {{ Js::from($conversion['service_id']) }}
    });
}
</script>
@endif
@endif
@endif
