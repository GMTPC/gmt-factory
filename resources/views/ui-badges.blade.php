@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <h1 class="text-gray-900 text-xl font-bold mb-0">Badge</h1>
        </div>

        <!-- Start grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-6">
            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h2 class="text-gray-900 text-lg font-bold mb-0">Default Badge</h2>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="badge rounded-md text-xs font-medium bg-primary text-white">Primary</span>
                    <span class="badge rounded-md text-xs font-medium bg-dark text-white">Dark</span>
                    <span class="badge rounded-md text-xs font-medium bg-success text-white">Success</span>
                    <span class="badge rounded-md text-xs font-medium bg-warning text-white">Warning</span>
                    <span class="badge rounded-md text-xs font-medium bg-info text-white">Info</span>
                    <span class="badge rounded-md text-xs font-medium bg-danger text-white">Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-primary text-white"&gt;Primary&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-dark text-white"&gt;Dark&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-success text-white"&gt;Success&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-warning text-white"&gt;Warning&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-info text-white"&gt;Info&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-danger text-white"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h3 class="text-gray-900 text-lg font-bold mb-0">Rounded Badge</h3>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="badge rounded-full text-xs font-medium bg-primary text-white">Primary</span>
                    <span class="badge rounded-full text-xs font-medium bg-dark text-white">Dark</span>
                    <span class="badge rounded-full text-xs font-medium bg-success text-white">Success</span>
                    <span class="badge rounded-full text-xs font-medium bg-warning text-white">Warning</span>
                    <span class="badge rounded-full text-xs font-medium bg-info text-white">Info</span>
                    <span class="badge rounded-full text-xs font-medium bg-danger text-white">Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-primary text-white"&gt;Primary&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-dark text-white"&gt;Dark&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-success text-white"&gt;Success&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-warning text-white"&gt;Warning&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-info text-white"&gt;Info&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-danger text-white"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h4 class="text-gray-900 text-lg font-bold mb-0">Ghost Badge Flat</h4>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary">Primary</span>
                    <span class="badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color">Dark</span>
                    <span class="badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success">Success</span>
                    <span class="badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning">Warning</span>
                    <span class="badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info">Info</span>
                    <span class="badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger">Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;Primary&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;Dark&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;Success&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;Warning&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;Info&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Rounded</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary">Primary</span>
                    <span class="badge rounded-full text-xs font-medium bg-light text-dark border border-border-color">Dark</span>
                    <span class="badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success">Success</span>
                    <span class="badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning">Warning</span>
                    <span class="badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info">Info</span>
                    <span class="badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger">Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;Primary&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"&gt;Dark&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"&gt;Success&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;Warning&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"&gt;Info&lt;/span&gt;
        &lt;span class="badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Default Badge Sizes</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content">
                    <div class="flex items-start flex-wrap justify-between mb-5">
                        <span class="badge-small rounded-md text-xs font-medium bg-primary text-white">Primary</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-dark text-white">Dark</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-success text-white">Success</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-warning text-white">Warning</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-info text-white">Info</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-danger text-white">Error</span>
                    </div>
                    <div class="flex items-start flex-wrap gap-4">
                        <span class="badge rounded-md text-xs font-medium bg-primary text-white">Primary</span>
                        <span class="badge rounded-md text-xs font-medium bg-dark text-white">Dark</span>
                        <span class="badge rounded-md text-xs font-medium bg-success text-white">Success</span>
                        <span class="badge rounded-md text-xs font-medium bg-warning text-white">Warning</span>
                        <span class="badge rounded-md text-xs font-medium bg-info text-white">Info</span>
                        <span class="badge rounded-md text-xs font-medium bg-danger text-white">Error</span>
                    </div>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap justify-between mb-5"&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-primary text-white"&gt;Primary&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-dark text-white"&gt;Dark&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-success text-white"&gt;Success&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-warning text-white"&gt;Warning&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-info text-white"&gt;Info&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-danger text-white"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-primary text-white"&gt;Primary&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-dark text-white"&gt;Dark&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-success text-white"&gt;Success&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-warning text-white"&gt;Warning&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-info text-white"&gt;Info&lt;/span&gt;
        &lt;span class="badge rounded-md text-xs font-medium bg-danger text-white"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Sizes</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content">
                    <div class="flex items-start flex-wrap justify-between mb-5">
                        <span class="badge-small rounded-md text-xs font-medium bg-primary-transparent text-primary border border-primary">Primary</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-light text-dark border border-border-color">Dark</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-success-transparent text-success border border-success">Success</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-warning-transparent text-warning border border-warning">Warning</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-info-transparent text-info border border-info">Info</span>
                        <span class="badge-small rounded-md text-xs font-medium bg-danger-transparent text-danger border border-danger">Error</span>
                    </div>
                    <div class="flex items-start flex-wrap gap-4">
                        <span class="badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary">Primary</span>
                        <span class="badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color">Dark</span>
                        <span class="badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success">Success</span>
                        <span class="badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning">Warning</span>
                        <span class="badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info">Info</span>
                        <span class="badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger">Error</span>
                    </div>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap justify-between mb-5"&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;Primary&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-light text-dark border border-border-color"&gt;Dark&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-success-transparent text-success border border-success"&gt;Success&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;Warning&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-info-transparent text-info border border-info"&gt;Info&lt;/span&gt;
        &lt;span class="badge-small rounded-md text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;Primary&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;Dark&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;Success&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;Warning&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;Info&lt;/span&gt;
        &lt;span class="badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Default Badge with Left Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"><i class="icon icon-check me-1"></i> Primary</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"><i class="icon icon-check me-1"></i> Dark</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"><i class="icon icon-check me-1"></i> Success</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"><i class="icon icon-check me-1"></i> Warning</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"><i class="icon icon-check me-1"></i> Info</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"><i class="icon icon-check me-1"></i> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Rounded Badge with Left Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"><i class="icon icon-check me-1"></i> Primary</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"><i class="icon icon-check me-1"></i> Dark</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"><i class="icon icon-check me-1"></i> Success</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"><i class="icon icon-check me-1"></i> Warning</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"><i class="icon icon-check me-1"></i> Info</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"><i class="icon icon-check me-1"></i> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Flat with Left Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"><i class="icon icon-check me-1"></i> Primary</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"><i class="icon icon-check me-1"></i> Dark</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"><i class="icon icon-check me-1"></i> Success</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"><i class="icon icon-check me-1"></i> Warning</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"><i class="icon icon-check me-1"></i> Info</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"><i class="icon icon-check me-1"></i> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Rounded with Left Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"><i class="icon icon-check me-1"></i> Primary</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"><i class="icon icon-check me-1"></i> Dark</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"><i class="icon icon-check me-1"></i> Success</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"><i class="icon icon-check me-1"></i> Warning</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"><i class="icon icon-check me-1"></i> Info</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"><i class="icon icon-check me-1"></i> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;i class="icon icon-check me-1"&gt;&lt;/i&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Default Badge with Right Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white">Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white">Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white">Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white">Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white">Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white">Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"&gt;Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"&gt;Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"&gt;Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"&gt;Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"&gt;Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"&gt;Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Rounded Badge with Right Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white">Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white">Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white">Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white">Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white">Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white">Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"&gt;Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"&gt;Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"&gt;Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"&gt;Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"&gt;Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"&gt;Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Flat with Right Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary">Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color">Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success">Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning">Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info">Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger">Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Rounded with Right Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary">Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color">Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success">Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning">Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info">Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger">Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"&gt;Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"&gt;Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"&gt;Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Default Badge with Dot</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Primary</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Dark</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Success</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Warning</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Info</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Rounded Badge with Dot</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Primary</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Dark</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Success</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Warning</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Info</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"><span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"></span> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"&gt;&lt;span class="bg-white w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white"&gt;&lt;/span&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Flat with Dot</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"><span class="bg-primary w-1.5 h-1.5 block rounded-full me-1"></span> Primary</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"><span class="bg-dark w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white!"></span> Dark</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"><span class="bg-success w-1.5 h-1.5 block rounded-full me-1"></span> Success</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"><span class="bg-warning w-1.5 h-1.5 block rounded-full me-1"></span> Warning</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"><span class="bg-info w-1.5 h-1.5 block rounded-full me-1"></span> Info</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"><span class="bg-danger w-1.5 h-1.5 block rounded-full me-1"></span> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;span class="bg-primary w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;span class="bg-dark w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;span class="bg-success w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;span class="bg-warning w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;span class="bg-info w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;span class="bg-danger w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Rounded with Dot</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"><span class="bg-primary w-1.5 h-1.5 block rounded-full me-1"></span> Primary</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"><span class="bg-dark w-1.5 h-1.5 block rounded-full me-1 dark:bg-dark-white!"></span> Dark</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"><span class="bg-success w-1.5 h-1.5 block rounded-full me-1"></span> Success</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"><span class="bg-warning w-1.5 h-1.5 block rounded-full me-1"></span> Warning</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"><span class="bg-info w-1.5 h-1.5 block rounded-full me-1"></span> Info</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"><span class="bg-danger w-1.5 h-1.5 block rounded-full me-1"></span> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;span class="bg-primary w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;span class="bg-dark w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;span class="bg-success w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;span class="bg-warning w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;span class="bg-info w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;span class="bg-danger w-1.5 h-1.5 block rounded-full me-1"&gt;&lt;/span&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Default Badge with Image</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Primary</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Dark</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Success</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Warning</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Info</span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Rounded Badge with Image</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Primary</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Dark</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Success</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Warning</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Info</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Flat with Image</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Primary</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Dark</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Success</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Warning</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Info</span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Rounded with Image</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Primary</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Dark</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Success</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Warning</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Info</span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Error</span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Primary&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Dark&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Success&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Warning&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Info&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Error&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Default Badge with Image & Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-primary text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-dark text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-success text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-warning text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-info text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-md text-xs font-medium bg-danger text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5 mb-6">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Rounded Badge with Image & Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-dark text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger text-white"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Flat with Image & Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"> Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-lg text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-xs" alt="avatar"&gt; Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card bg-white rounded-xl border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Ghost Badge Rounded with Image & Icon</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-start flex-wrap gap-4">
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Primary <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Dark <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Success <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Warning <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Info <i class="icon icon-x ms-1"></i></span>
                    <span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"><img src="{{URL::asset('/build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"> Error <i class="icon icon-x ms-1"></i></span>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[450px] mb-[-21px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-start flex-wrap gap-4"&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-primary-transparent text-primary border border-primary"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Primary &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-light text-dark border border-border-color"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Dark &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-success-transparent text-success border border-success"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Success &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-warning-transparent text-warning border border-warning"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Warning &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-info-transparent text-info border border-info"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Info &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
        &lt;span class="inline-flex items-center badge rounded-full text-xs font-medium bg-danger-transparent text-danger border border-danger"&gt;&lt;img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="w-3 h-3 me-1 rounded-full" alt="avatar"&gt; Error &lt;i class="icon icon-x ms-1"&gt;&lt;/i&gt;&lt;/span&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

        </div>
        <!-- End grid -->
    </div>
    <!-- End Content -->

@endsection
