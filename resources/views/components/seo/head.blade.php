<title>{{ $seo->title }}</title>
@if($seo->description)
<meta name="description" content="{{ $seo->description }}">
@endif
<link rel="canonical" href="{{ $seo->canonical }}">
@foreach($seo->alternateUrls as $altLocale => $altUrl)
<link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}">
@endforeach
@if($seo->xDefaultUrl)
<link rel="alternate" hreflang="x-default" href="{{ $seo->xDefaultUrl }}">
@endif
<meta name="robots" content="{{ $seo->robots }}">
<meta property="og:title" content="{{ $seo->title }}">
@if($seo->description)
<meta property="og:description" content="{{ $seo->description }}">
@endif
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:locale" content="{{ $seo->locale === 'en' ? 'en_US' : 'vi_VN' }}">
@if($seo->ogImage)
<meta property="og:image" content="{{ $seo->ogImage }}">
@endif
<meta name="twitter:card" content="{{ $seo->twitterCard }}">
<meta name="twitter:title" content="{{ $seo->title }}">
@if($seo->description)
<meta name="twitter:description" content="{{ $seo->description }}">
@endif
@if($seo->ogImage)
<meta name="twitter:image" content="{{ $seo->ogImage }}">
@endif
{!! $seo->jsonLdScript() !!}
