@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <h1 class="text-gray-900 text-xl font-bold mb-0">Data Tables</h1>
        </div>

        <!-- Start grid -->
        <div class="grid grid-cols-1">
            <div class="preview-card bg-white rounded-md border border-border-color p-5 pb-0">
                <div class="pb-5 mb-5 flex items-center xxl:gap-0 gap-3 justify-between flex-wrap border-b border-border-color">
                    <h2 class="text-gray-900 text-lg font-bold mb-0">Data Table</h2>
                    <button type="button" data-toggle="code" class="flex items-center gap-2 border py-1.5 px-2.5 text-sm border-border-color font-semibold rounded-md bg-light-200 dark:bg-gray-100 focus:bg-primary focus:border-primary focus:text-white text-gray-900 dark:text-dark dark:focus:text-dark">
                        <i class="icon icon-eye"></i>
                        <span class="code-btn">Show Code</span>
                    </button>
                </div> <!-- end col -->

                <div class="pb-3 flex items-center justify-between gap-3 flex-wrap">
                    <h3 class="text-[17.5px]">Data List</h3>
                    <div class="flex items-center">
                        <div id="tablesearch"></div>
                    </div>
                </div> <!-- end col -->

                <div class="preview-content overflow-auto">
                    <table class="datatable border border-border-color">
                        <thead class="border border-border-color">
                            <tr>
                                <th class="px-4 py-2 bg-light text-dark text-start">Name</th>
                                <th class="px-4 py-2 bg-light text-dark text-start">Position</th>
                                <th class="px-4 py-2 bg-light text-dark text-start">Office</th>
                                <th class="px-4 py-2 bg-light text-dark text-start">Age</th>
                                <th class="px-4 py-2 bg-light text-dark text-start">Start date</th>
                                <th class="px-4 py-2 bg-light text-dark text-start">Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Tiger Nixon</td>
                                <td class="px-4 py-3 text-start">System Architect</td>
                                <td class="px-4 py-3 text-start">Edinburgh</td>
                                <td class="px-4 py-3 text-start">61</td>
                                <td class="px-4 py-3 text-start">2011 Apr 25</td>
                                <td class="px-4 py-3 text-start">$320,800</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Garrett Winters</td>
                                <td class="px-4 py-3 text-start">Accountant</td>
                                <td class="px-4 py-3 text-start">Tokyo</td>
                                <td class="px-4 py-3 text-start">63</td>
                                <td class="px-4 py-3 text-start">2011 Jul 25</td>
                                <td class="px-4 py-3 text-start">$170,750</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Ashton Cox</td>
                                <td class="px-4 py-3 text-start">Junior Technical Author</td>
                                <td class="px-4 py-3 text-start">San Francisco</td>
                                <td class="px-4 py-3 text-start">66</td>
                                <td class="px-4 py-3 text-start">2009 Jan 12</td>
                                <td class="px-4 py-3 text-start">$86,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Cedric Kelly</td>
                                <td class="px-4 py-3 text-start">Senior Javascript Developer</td>
                                <td class="px-4 py-3 text-start">Senior Javascript Developer</td>
                                <td class="px-4 py-3 text-start">22</td>
                                <td class="px-4 py-3 text-start">2012 Mar 29</td>
                                <td class="px-4 py-3 text-start">$433,060</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Airi Satou</td>
                                <td class="px-4 py-3 text-start">Accountant</td>
                                <td class="px-4 py-3 text-start">Tokyo</td>
                                <td class="px-4 py-3 text-start">33</td>
                                <td class="px-4 py-3 text-start">2008 Nov 28</td>
                                <td class="px-4 py-3 text-start">$162,700</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Brielle Williamson</td>
                                <td class="px-4 py-3 text-start">Integration Specialist</td>
                                <td class="px-4 py-3 text-start">New York</td>
                                <td class="px-4 py-3 text-start">61</td>
                                <td class="px-4 py-3 text-start">2012 Dec 02</td>
                                <td class="px-4 py-3 text-start">$372,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Herrod Chandler</td>
                                <td class="px-4 py-3 text-start">Sales Assistant</td>
                                <td class="px-4 py-3 text-start">San Francisco</td>
                                <td class="px-4 py-3 text-start">59</td>
                                <td class="px-4 py-3 text-start">2012 Aug 06</td>
                                <td class="px-4 py-3 text-start">$137,500</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Rhona Davidson</td>
                                <td class="px-4 py-3 text-start">Integration Specialist</td>
                                <td class="px-4 py-3 text-start">Tokyo</td>
                                <td class="px-4 py-3 text-start">55</td>
                                <td class="px-4 py-3 text-start">2010 Oct 14</td>
                                <td class="px-4 py-3 text-start">$327,900</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Colleen Hurst</td>
                                <td class="px-4 py-3 text-start">Javascript Developer</td>
                                <td class="px-4 py-3 text-start">San Francisco</td>
                                <td class="px-4 py-3 text-start">39</td>
                                <td class="px-4 py-3 text-start">2009 Sep 15</td>
                                <td class="px-4 py-3 text-start">$205,500</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Sonya Frost</td>
                                <td class="px-4 py-3 text-start">Software Engineer</td>
                                <td class="px-4 py-3 text-start">Edinburgh</td>
                                <td class="px-4 py-3 text-start">23</td>
                                <td class="px-4 py-3 text-start">2008 Dec 13</td>
                                <td class="px-4 py-3 text-start">$103,600</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Jena Gaines</td>
                                <td class="px-4 py-3 text-start">Office Manager</td>
                                <td class="px-4 py-3 text-start">London</td>
                                <td class="px-4 py-3 text-start">30</td>
                                <td class="px-4 py-3 text-start">2008 Dec 19</td>
                                <td class="px-4 py-3 text-start">$90,560</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Quinn Flynn</td>
                                <td class="px-4 py-3 text-start">Support Lead</td>
                                <td class="px-4 py-3 text-start">Edinburgh</td>
                                <td class="px-4 py-3 text-start">22</td>
                                <td class="px-4 py-3 text-start">2013 Mar 03</td>
                                <td class="px-4 py-3 text-start">$342,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Charde Marshall</td>
                                <td class="px-4 py-3 text-start">Regional Director</td>
                                <td class="px-4 py-3 text-start">San Francisco</td>
                                <td class="px-4 py-3 text-start">36</td>
                                <td class="px-4 py-3 text-start">2008 Oct 16</td>
                                <td class="px-4 py-3 text-start">$470,600</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Haley Kennedy</td>
                                <td class="px-4 py-3 text-start">Senior Marketing Designer</td>
                                <td class="px-4 py-3 text-start">London</td>
                                <td class="px-4 py-3 text-start">43</td>
                                <td class="px-4 py-3 text-start">2012 Dec 18</td>
                                <td class="px-4 py-3 text-start">$313,500</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Tatyana Fitzpatrick</td>
                                <td class="px-4 py-3 text-start">Regional Director</td>
                                <td class="px-4 py-3 text-start">London</td>
                                <td class="px-4 py-3 text-start">19</td>
                                <td class="px-4 py-3 text-start">2010 Mar 17</td>
                                <td class="px-4 py-3 text-start">$385,750</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Michael Silva</td>
                                <td class="px-4 py-3 text-start">Marketing Designer</td>
                                <td class="px-4 py-3 text-start">London</td>
                                <td class="px-4 py-3 text-start">66</td>
                                <td class="px-4 py-3 text-start">2012 Nov 27</td>
                                <td class="px-4 py-3 text-start">$198,500</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Paul Byrd</td>
                                <td class="px-4 py-3 text-start">Chief Financial Officer (CFO)</td>
                                <td class="px-4 py-3 text-start">New York</td>
                                <td class="px-4 py-3 text-start">64</td>
                                <td class="px-4 py-3 text-start">2010 Jun 09</td>
                                <td class="px-4 py-3 text-start">$725,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Gloria Little</td>
                                <td class="px-4 py-3 text-start">Systems Administrator</td>
                                <td class="px-4 py-3 text-start">New York</td>
                                <td class="px-4 py-3 text-start">59</td>
                                <td class="px-4 py-3 text-start">2009 Apr 10</td>
                                <td class="px-4 py-3 text-start">$237,500</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Bradley Greer</td>
                                <td class="px-4 py-3 text-start">Software Engineer</td>
                                <td class="px-4 py-3 text-start">London</td>
                                <td class="px-4 py-3 text-start">41</td>
                                <td class="px-4 py-3 text-start">2012 Oct 13</td>
                                <td class="px-4 py-3 text-start">$132,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Dai Rios</td>
                                <td class="px-4 py-3 text-start">Personnel Lead</td>
                                <td class="px-4 py-3 text-start">Edinburgh</td>
                                <td class="px-4 py-3 text-start">35</td>
                                <td class="px-4 py-3 text-start">2012 Sep 26</td>
                                <td class="px-4 py-3 text-start">$217,500</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Jenette Caldwell</td>
                                <td class="px-4 py-3 text-start">Development Lead</td>
                                <td class="px-4 py-3 text-start">New York</td>
                                <td class="px-4 py-3 text-start">30</td>
                                <td class="px-4 py-3 text-start">2011 Sep 03</td>
                                <td class="px-4 py-3 text-start">$345,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Yuri Berry</td>
                                <td class="px-4 py-3 text-start">Chief Marketing Officer (CMO)</td>
                                <td class="px-4 py-3 text-start">New York</td>
                                <td class="px-4 py-3 text-start">40</td>
                                <td class="px-4 py-3 text-start">2009 Jun 25</td>
                                <td class="px-4 py-3 text-start">$675,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Caesar Vance</td>
                                <td class="px-4 py-3 text-start">Pre-Sales Support</td>
                                <td class="px-4 py-3 text-start">New York</td>
                                <td class="px-4 py-3 text-start">21</td>
                                <td class="px-4 py-3 text-start">2011 Dec 12</td>
                                <td class="px-4 py-3 text-start">$106,450</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Doris Wilder</td>
                                <td class="px-4 py-3 text-start">Sales Assistant</td>
                                <td class="px-4 py-3 text-start">Sidney</td>
                                <td class="px-4 py-3 text-start">23</td>
                                <td class="px-4 py-3 text-start">2010 Sep 20</td>
                                <td class="px-4 py-3 text-start">$85,600</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Angelica Ramos</td>
                                <td class="px-4 py-3 text-start">Chief Executive Officer (CEO)</td>
                                <td class="px-4 py-3 text-start">London</td>
                                <td class="px-4 py-3 text-start">47</td>
                                <td class="px-4 py-3 text-start">2009 Oct 09</td>
                                <td class="px-4 py-3 text-start">$1,200,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Gavin Joyce</td>
                                <td class="px-4 py-3 text-start">Developer</td>
                                <td class="px-4 py-3 text-start">Edinburgh</td>
                                <td class="px-4 py-3 text-start">42</td>
                                <td class="px-4 py-3 text-start">2010 Dec 22</td>
                                <td class="px-4 py-3 text-start">$92,575</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Jennifer Chang</td>
                                <td class="px-4 py-3 text-start">Regional Director</td>
                                <td class="px-4 py-3 text-start">Singapore</td>
                                <td class="px-4 py-3 text-start">28</td>
                                <td class="px-4 py-3 text-start">2010 Nov 14</td>
                                <td class="px-4 py-3 text-start">$357,650</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Brenden Wagner</td>
                                <td class="px-4 py-3 text-start">Software Engineer</td>
                                <td class="px-4 py-3 text-start">San Francisco</td>
                                <td class="px-4 py-3 text-start">28</td>
                                <td class="px-4 py-3 text-start">2011 Jun 07</td>
                                <td class="px-4 py-3 text-start">$206,850</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Fiona Green</td>
                                <td class="px-4 py-3 text-start">Chief Operating Officer (COO)</td>
                                <td class="px-4 py-3 text-start">San Francisco</td>
                                <td class="px-4 py-3 text-start">48</td>
                                <td class="px-4 py-3 text-start">2010 Mar 11</td>
                                <td class="px-4 py-3 text-start">$850,000</td>
                            </tr>
                            <tr class="border-b border-border-color">
                                <td class="px-4 py-3 text-start">Shou Itou</td>
                                <td class="px-4 py-3 text-start">Regional Marketing</td>
                                <td class="px-4 py-3 text-start">Tokyo</td>
                                <td class="px-4 py-3 text-start">20</td>
                                <td class="px-4 py-3 text-start">2011 Aug 14</td>
                                <td class="px-4 py-3 text-start">$163,000</td>
                            </tr>

                        </tbody>
                    </table>
                    <div class="grid grid-cols-12 px-4 gap-2 py-3">
                        <div class="sm:col-span-4 col-span-12">
                            <div id="tablelength" class="flex justify-center sm:block"></div>
                        </div>
                        <div class="sm:col-span-4 col-span-12 flex items-center justify-center">
                            <div id="tablepage" class="flex justify-center sm:block"></div>
                        </div>
                        <div class="sm:col-span-4 col-span-12 flex items-center sm:justify-end justify-center">
                            <div id="tableinfo" class="flex justify-center sm:block"></div>
                        </div>
                    </div>
                </div> <!-- end col -->

    <pre class="code hidden relative mt-4 p-0! bg-dark text-gray-100 text-sm  overflow-hidden">
    <button type="button" data-copy class="sticky float-end top-3 right-3 z-10 bg-gray-800 text-white text-sm px-2.5 py-1.5 rounded-md hover:bg-gray-700 flex items-center gap-1"><i class="icon icon-copy"></i><span>Copy</span></button>
    <code class="language-html block w-full max-h-[400px] mb-[-42px] mt-[-10px]! overflow-auto p-4 pr-16">
    &lt;div class="overflow-auto"&gt;
        &lt;table class="datatable border border-border-color"&gt;
            &lt;thead class="border border-border-color"&gt;
                &lt;tr&gt;
                    &lt;th class="px-4 py-2 bg-light text-dark text-start"&gt;Name&lt;/th&gt;
                    &lt;th class="px-4 py-2 bg-light text-dark text-start"&gt;Position&lt;/th&gt;
                    &lt;th class="px-4 py-2 bg-light text-dark text-start"&gt;Office&lt;/th&gt;
                    &lt;th class="px-4 py-2 bg-light text-dark text-start"&gt;Age&lt;/th&gt;
                    &lt;th class="px-4 py-2 bg-light text-dark text-start"&gt;Start date&lt;/th&gt;
                    &lt;th class="px-4 py-2 bg-light text-dark text-start"&gt;Salary&lt;/th&gt;
                &lt;/tr&gt;
            &lt;/thead&gt;
            &lt;tbody&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Tiger Nixon&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;System Architect&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Edinburgh&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;61&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2011 Apr 25&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$320,800&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Garrett Winters&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Accountant&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Tokyo&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;63&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2011 Jul 25&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$170,750&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Ashton Cox&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Junior Technical Author&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;San Francisco&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;66&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2009 Jan 12&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$86,000&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Cedric Kelly&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Senior Javascript Developer&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Senior Javascript Developer&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;22&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2012 Mar 29&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$433,060&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Airi Satou&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Accountant&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Tokyo&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;33&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2008 Nov 28&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$162,700&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Brielle Williamson&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Integration Specialist&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;New York&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;61&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2012 Dec 02&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$372,000&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Herrod Chandler&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Sales Assistant&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;San Francisco&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;59&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2012 Aug 06&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$137,500&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Rhona Davidson&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Integration Specialist&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Tokyo&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;55&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2010 Oct 14&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$327,900&lt;/td&gt;
                &lt;/tr&gt;
                &lt;tr class="border-b border-border-color"&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Colleen Hurst&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;Javascript Developer&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;San Francisco&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;39&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;2009 Sep 15&lt;/td&gt;
                    &lt;td class="px-4 py-3 text-start"&gt;$205,500&lt;/td&gt;
                &lt;/tr&gt;
            &lt;/tbody&gt;
        &lt;/table&gt;
    &lt;/div&gt;
    </code>
    </pre>

            </div>
        </div>
        <!-- End grid -->
    </div>
    <!-- End Content -->

@endsection
