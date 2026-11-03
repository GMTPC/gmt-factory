@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <h1 class="text-gray-900 text-xl font-bold mb-0">Form Select</h1>
        </div>

        <!-- Start Grid -->
        <div class="grid grid-cols-1 gap-6">
            <div class="preview-card bg-white rounded-md border border-border-color p-5">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h2 class="text-gray-900 text-lg font-bold mb-0">Choices</h2>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-sm border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div> <!-- end col -->

                <div class="preview-content grid grid-cols-12 gap-5">
                    <div class="col-span-12 sm:col-span-6">
                        <label for="choices-single-no-sorting" class="block text-sm font-medium mb-2 text-dark">Default</label>
                        <select class="form-input block w-full bg-white border-border-color rounded-lg focus:border-primary disabled:opacity-50 disabled:pointer-events-none" id="choices-single-no-sorting" name="choices-single-no-sorting" data-choices data-choices-sorting-false>
                            <option value="Madrid">Madrid</option>
                            <option value="Toronto">Toronto</option>
                            <option value="Vancouver">Vancouver</option>
                            <option value="London">London</option>
                            <option value="Manchester">Manchester</option>
                            <option value="Liverpool">Liverpool</option>
                            <option value="Paris">Paris</option>
                            <option value="Malaga">Malaga</option>
                            <option value="Washington" disabled>Washington</option>
                            <option value="Lyon">Lyon</option>
                            <option value="Marseille">Marseille</option>
                            <option value="Hamburg">Hamburg</option>
                            <option value="Munich">Munich</option>
                            <option value="Barcelona">Barcelona</option>
                            <option value="Berlin">Berlin</option>
                            <option value="Montreal">Montreal</option>
                            <option value="New York">New York</option>
                            <option value="Michigan">Michigan</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="choices-single-no-search" class="block text-sm font-medium mb-2 text-dark">Options added</label>
                        <select class="form-input block w-full bg-white border-border-color rounded-lg focus:border-primary disabled:opacity-50 disabled:pointer-events-none" id="choices-single-no-search" name="choices-single-no-search" data-choices data-choices-search-false data-choices-removeItem>
                            <option value="Zero">Zero</option>
                            <option value="One">One</option>
                            <option value="Two">Two</option>
                            <option value="Three">Three</option>
                            <option value="Four">Four</option>
                            <option value="Five">Five</option>
                            <option value="Six">Six</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="choices-multiple-default" class="block text-sm font-medium mb-2 text-dark">Multiple Select</label>
                        <select class="form-control" id="choices-multiple-default" data-choices name="choices-multiple-default" multiple>
                            <option value="Choice 1" selected>Choice 1</option>
                            <option value="Choice 2">Choice 2</option>
                            <option value="Choice 3">Choice 3</option>
                            <option value="Choice 4">Choice 4</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="choices-multiple-remove-button" class="block text-sm font-medium mb-2 text-dark">Multiple Select With Remove</label>
                        <select class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="choices-multiple-remove-button" multiple>
                            <option value="Choice 1" selected>Choice 1</option>
                            <option value="Choice 2">Choice 2</option>
                            <option value="Choice 3">Choice 3</option>
                            <option value="Choice 4">Choice 4</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="choices-text-remove-button" class="block text-sm font-medium mb-2 text-dark">Text Inputs With Remove</label>
                        <input class="form-control" id="choices-text-remove-button" data-choices data-choices-limit="3" data-choices-removeItem type="text" value="Task 1">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="choices-text-unique-values" class="block text-sm font-medium mb-2 text-dark">Text Inputs Add </label>
                        <input class="form-control" id="choices-text-unique-values" data-choices data-choices-text-unique-true type="text" value="Project A, Project B">
                    </div>
                </div> <!-- end col -->

    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-sm px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[250px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="preview-content grid grid-cols-1 lg:grid-cols-2 gap-6"&gt;
        &lt;div class="mb-4"&gt;
            &lt;label class="block text-sm font-medium mb-2 text-dark"&gt;Default&lt;/label&gt;
            &lt;select class="form-input block w-full bg-white border-border-color rounded-lg focus:border-primary disabled:opacity-50 disabled:pointer-events-none" id="choices-single-no-sorting" name="choices-single-no-sorting" data-choices data-choices-sorting-false&gt;
                &lt;option value="Madrid"&gt;Madrid&lt;/option&gt;
                &lt;option value="Toronto"&gt;Toronto&lt;/option&gt;
                &lt;option value="Vancouver"&gt;Vancouver&lt;/option&gt;
                &lt;option value="London"&gt;London&lt;/option&gt;
                &lt;option value="Manchester"&gt;Manchester&lt;/option&gt;
                &lt;option value="Liverpool"&gt;Liverpool&lt;/option&gt;
                &lt;option value="Paris"&gt;Paris&lt;/option&gt;
                &lt;option value="Malaga"&gt;Malaga&lt;/option&gt;
                &lt;option value="Washington" disabled&gt;Washington&lt;/option&gt;
                &lt;option value="Lyon"&gt;Lyon&lt;/option&gt;
                &lt;option value="Marseille"&gt;Marseille&lt;/option&gt;
                &lt;option value="Hamburg"&gt;Hamburg&lt;/option&gt;
                &lt;option value="Munich"&gt;Munich&lt;/option&gt;
                &lt;option value="Barcelona"&gt;Barcelona&lt;/option&gt;
                &lt;option value="Berlin"&gt;Berlin&lt;/option&gt;
                &lt;option value="Montreal"&gt;Montreal&lt;/option&gt;
                &lt;option value="New York"&gt;New York&lt;/option&gt;
                &lt;option value="Michigan"&gt;Michigan&lt;/option&gt;
            &lt;/select&gt;
        &lt;/div&gt;
        &lt;div class="mb-4"&gt;
            &lt;label class="block text-sm font-medium mb-2 text-dark"&gt;Options added&lt;/label&gt;
            &lt;select class="form-input block w-full bg-white border-border-color rounded-lg focus:border-primary disabled:opacity-50 disabled:pointer-events-none" id="choices-single-no-search" name="choices-single-no-search" data-choices data-choices-search-false data-choices-removeItem&gt;
                &lt;option value="Zero"&gt;Zero&lt;/option&gt;
                &lt;option value="One"&gt;One&lt;/option&gt;
                &lt;option value="Two"&gt;Two&lt;/option&gt;
                &lt;option value="Three"&gt;Three&lt;/option&gt;
                &lt;option value="Four"&gt;Four&lt;/option&gt;
                &lt;option value="Five"&gt;Five&lt;/option&gt;
                &lt;option value="Six"&gt;Six&lt;/option&gt;
            &lt;/select&gt;
        &lt;/div&gt;
        &lt;div class="mb-4"&gt;
            &lt;label class="block text-sm font-medium mb-2 text-dark"&gt;Multiple Select&lt;/label&gt;
            &lt;select class="form-control" id="choices-multiple-default" data-choices name="choices-multiple-default" multiple&gt;
                &lt;option value="Choice 1" selected&gt;Choice 1&lt;/option&gt;
                &lt;option value="Choice 2"&gt;Choice 2&lt;/option&gt;
                &lt;option value="Choice 3"&gt;Choice 3&lt;/option&gt;
                &lt;option value="Choice 4"&gt;Choice 4&lt;/option&gt;
            &lt;/select&gt;
        &lt;/div&gt;
        &lt;div class="mb-4"&gt;
            &lt;label class="block text-sm font-medium mb-2 text-dark"&gt;Multiple Select With Remove&lt;/label&gt;
            &lt;select class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="choices-multiple-remove-button" multiple&gt;
                &lt;option value="Choice 1" selected&gt;Choice 1&lt;/option&gt;
                &lt;option value="Choice 2"&gt;Choice 2&lt;/option&gt;
                &lt;option value="Choice 3"&gt;Choice 3&lt;/option&gt;
                &lt;option value="Choice 4"&gt;Choice 4&lt;/option&gt;
            &lt;/select&gt;
        &lt;/div&gt;
        &lt;div class="mb-4"&gt;
            &lt;label class="block text-sm font-medium mb-2 text-dark"&gt;Text Inputs With Remove&lt;/label&gt;
            &lt;input class="form-control" id="choices-text-remove-button" data-choices data-choices-limit="3" data-choices-removeItem type="text" value="Task-1"&gt;
        &lt;/div&gt;
        &lt;div class="mb-4"&gt;
            &lt;label class="block text-sm font-medium mb-2 text-dark"&gt;Text Inputs Add &lt;/label&gt;
            &lt;input class="form-control" id="choices-text-unique-values" data-choices data-choices-text-unique-true type="text" value="Project-A, Project-B"&gt;
        &lt;/div&gt;
    &lt;/div&gt;
    </code>
    </pre>

            </div>

        </div>
        <!-- End grid -->
    </div>
    <!-- End Content -->

@endsection
