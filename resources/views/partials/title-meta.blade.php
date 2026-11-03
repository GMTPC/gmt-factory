@php
    $filename = Route::currentRouteName(); // use route name instead of PHP_SELF

    $acronyms = ['ui', 'ai', 'pos', 'api', 'js', 'crm', 'qr', 'hr', 'sla', 'hrm', 'faq', 'rtl'];

    if ($filename === 'index' || $filename === '/' || $filename === 'layout-fullwidth' || $filename === 'layout-hidden' || $filename === 'layout-hoverview' || $filename === 'layout-mini' || $filename === 'two-column' || $filename === 'rtl') {
        $title = 'HRM Dashboard';
    } else {
        $parts = explode('-', str_replace('ui-', '', strtolower($filename)));

        $hasIcon = false;
        $hasChart = false;
        $cleaned_parts = [];

        foreach ($parts as $part) {
            if ($part === 'icon') {
                $hasIcon = true;
                continue;
            }
            if ($part === 'chart') {
                $hasChart = true;
                continue;
            }
            if ($part === 'all') {
                continue;
            }
            $cleaned_parts[] = $part;
        }

        $formatted_parts = array_map(function ($word) use ($acronyms) {
            return in_array($word, $acronyms) ? strtoupper($word) : ucfirst($word);
        }, $cleaned_parts);

        if ($hasIcon) {
            $formatted_parts[] = 'Icons';
        }
        if ($hasChart) {
            $formatted_parts[] = 'Charts';
        }

        $title = implode(' ', $formatted_parts);
    }
@endphp

<!-- Meta Tags -->
<meta charset="utf-8">
<title>{{ $title }} | Dreams ERP - Tailwind CSS ERP Admin Dashboard Template</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Dreams ERP is a professional Tailwind CSS ERP Admin Dashboard Template featuring CRM, HRM, Inventory, Finance, Sales, Procurement, Project Management dashboards, reusable UI components, and responsive layouts.">
<meta name="author" content="Dreams Technologies">
