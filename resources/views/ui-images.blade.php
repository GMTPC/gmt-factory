@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <h1 class="text-gray-900 text-xl font-bold mb-0">Image</h1>
        </div>

        <!-- Start grid -->
        <div class="grid grid-cols-12 gap-6 items-baseline">
            <div class="preview-card md:col-span-6 sm:col-span-6  col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h2 class="text-gray-900 text-lg font-bold mb-0">Rounded Image</h2>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content">
                    <img class="rounded-lg" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;img class="rounded-lg" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card md:col-span-6 sm:col-span-6  col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h3 class="text-gray-900 text-lg font-bold mb-0">Image Thumbnail</h3>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content">
                    <img class="border-4 border-border-color rounded-lg" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;img class="border-2 border-border-color rounded-lg" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card md:col-span-6 sm:col-span-6  col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h4 class="text-gray-900 text-lg font-bold mb-0">Rounded Circle</h4>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-center justify-center">
                    <img class="rounded-full w-50" src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-center justify-center"&gt;
        &lt;img class="rounded-full w-50" src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" alt="image"&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card md:col-span-6 sm:col-span-6  col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Rounded Bordered</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-center justify-center">
                    <img class="rounded-full border-4 border-border-color w-50" src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-center justify-center"&gt;
        &lt;img class="rounded-full border-2 border-border-color w-50" src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" alt="image"&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card sm:col-span-6 col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Image Left Align</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content">
                    <img class="rounded-lg w-46" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;img class="rounded-lg w-46" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card sm:col-span-6 col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Image Center Align</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-center justify-center">
                    <img class="rounded-lg w-46" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-center justify-center"&gt;
        &lt;img class="rounded-lg w-46" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card xl:col-span-6 sm:col-span-12 col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Image Right Align</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content flex items-end justify-end">
                    <img class="rounded-lg w-46" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex items-end justify-end"&gt;
        &lt;img class="rounded-lg w-46" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

            <div class="preview-card col-span-12 bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h5>Figures</h5>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-xs border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div>
                <div class="preview-content grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <img class="rounded-lg w-auto mb-2" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                        <p class="text-start">A caption for the above image.</p>
                    </div>
                    <div>
                        <img class="rounded-lg w-auto mb-2" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                        <p class="text-center">A caption for the above image.</p>
                    </div>
                    <div>
                        <img class="rounded-lg w-auto mb-2" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="img">
                        <p class="text-end">A caption for the above image.</p>
                    </div>
                </div>
    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1">
        <i class="icon icon-copy"></i><span>Copy</span>
    </button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="flex gap-6"&gt;
        &lt;div&gt;
            &lt;img class="rounded-lg w-auto mb-2" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
            &lt;p class="text-start"&gt;A caption for the above image.&lt;/p&gt;
        &lt;/div&gt;

        &lt;div&gt;
            &lt;img class="rounded-lg w-auto mb-2" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
            &lt;p class="text-center"&gt;A caption for the above image.&lt;/p&gt;
        &lt;/div&gt;

        &lt;div&gt;
            &lt;img class="rounded-lg w-auto mb-2" src="{{URL::asset('build/img/card/card-02.jpg')}}" alt="image"&gt;
            &lt;p class="text-end"&gt;A caption for the above image.&lt;/p&gt;
        &lt;/div&gt;
    &lt;/div&gt;
    </code>
    </pre>
            </div> <!-- end card -->

        </div>
        <!-- End grid -->
    </div>
    <!-- End Content -->

@endsection
