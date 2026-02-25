<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        3/1/2019 10:51 AM
 * @brief
 */
namespace Crimson\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;

class AddCmsPagesContent implements
    DataPatchInterface,
    PatchVersionInterface
{


    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory
    )
    {
        $this->pageFactory = $pageFactory;
    }

    public function apply()
    {
        $page = $this->pageFactory->create();
//        $cmspage =  $page->load('partners', 'identifier');

        $pages = array(
            '1953/corvette/parts.html' => array(
                'title' => '1953 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1953 Corvette Parts',
                'content' =>'<p><img style="margin: 0px auto 10px; border: 2px solid #cccccc; display: block;" title="1953 Corvette Parts" alt="1953 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1953.jpg" /></p>
<p>June 30, 1953 - a day that will live in infamy among Corvette enthusiasts. The first two 1953 production Corvettes were completed in a makeshift factory in Flint, Michigan. Harley Earl\'s dream had become reality. Built by hand, both were sent to Chevrolet Engineering for evaluation. Corrections and modifications ensued. By year\'s end, 300 Corvettes had been built.</p>
<p>There was only one color option in 1953 - Polo White. All were convertibles with black canvas tops. Wheels and interior were red. No exceptions. Each 1953 had a Powerglide automatic transmission mated to 150 HP and a six cylinder engine with three carburetors and dual exhaust. Brake and fuel lines ran outside the chassis frame. The surge tank was unique in that its surface was smooth. Fancy footwork was a requirement for drivers, as the first 175 Corvettes used a foot-operated windshield washer assembly. Tube-type whitewall tires got the 1953 where it needed to go.</p>
<p>Although listed as options, all 1953 Corvettes were equipped with a signal-seeking AM radio and a heater. Base price for a piece of the American Dream - $3,498. Today, a 1953 with low mileage that has been certified by a judging panel could go for as much as $190,000. The oldest known Corvette, VIN 0003, could demand roughly $750,000.</p>
<p>The rarest of all Corvettes, 255 of the original 300 are accounted for today.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1954/corvette/parts.html' => array(
                'title' => '1954 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1954 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1953 Corvette Parts" alt="1953 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1954.jpg" /></p>
<p>In 1954, GM moved Corvette production from Michigan to St. Louis, Missouri. The newly renovated plant was designed to build 10,000 Corvettes each year at a rate of 50 per day. However, demand did not match projections and only 3,640 cars were built.</p>
<p>Base price for the sophomore Corvette was $2,774. While there were few design changes between the 1953 and 1954 models, a camshaft design change increased horsepower to 155bhp, compared to 150bhp in 1953. Vinyl interior trim and soft top were also included in the base price.</p>
<p>Another big difference in Corvette&rsquo;s second year was color options. Pennant Blue, Sportsman Red and Black joined Polo White as exterior color options and Beige joined Red as an interior option. Interestingly, some original owners reported Metallic Green or Metallic Bronze exteriors. While there is reason to believe that additional colors were indeed offered, there are no production records that confirm such offerings.</p>
<p>The Corvette&rsquo;s competition increased in 1954 with the release of the two-seat Ford Thunderbird. However, the battle was short lived as Ford dropped its two-seat model less than 5 years later.</p>
<p>Although rare, a driver quality 1954 Corvette roadster could sell for $55,000 - $65,000 today, in some instances upwards of $80,000 or more.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1955/corvette/parts.html' => array(
                'title' => '1955 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1955 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1955 Corvette Parts" alt="1955 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1955.jpg" /></p>
<p dir="ltr"><span>In 1955, Chevrolet installed its new 265ci V8 in all but seven 1955 Corvettes. Production for the year was low - only 700 cars were built. Although records for the year are largely a mystery, many assume production was low due to 1,100 &lsquo;54 cars sitting unsold on dealer lots.</span></p>
<p dir="ltr"><span>Chevy made several internal changes to the &lsquo;55 Corvette. First, it switch from a 6 volt electrical system to a 12-volt system. Chrome-plated valve covers and a manual heater cutoff valve rounded out internal modifications.</span></p>
<p dir="ltr"><span>&lsquo;55 Cars can be distinguished by the large gold &ldquo;V&rdquo; found in the script on each side of the car. Additionally, V8 &lsquo;55s also had VINs starting with the letter &ldquo;V&rdquo;.</span></p>
<p><span id="docs-internal-guid-7a974ce7-d4b6-3ec3-12d5-87bd6a0664c0"><span>Most &lsquo;55 cars shipped with a Powerglide Automatic Transmission, including all seven V6 cars. Eventually, GM began pairing its 3-speed manual with the V8, but available records indicate only 70-80 such cars were produced.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1956/corvette/parts.html' => array(
                'title' => '1956 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1956 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1956 Corvette Parts" alt="1956 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1956.jpg" /></p>
<p>The 1956 Corvette Convertible came flying out of the gate with a stunning new design and the horsepower to match. With a three-speed manual transmission mated to a 265ci V-8 making 210hp, the &rsquo;56 Corvette&rsquo;s army of 3,467 cars attacked American roads with gusto.</p>
<p>At a base price of $3,120, the 1956 Corvette was, for the first time, equipped with actual side windows and an optional hardtop, ready for all-weather driving. A redesigned heater brought fresh air into the cockpit and worked to keep the occupants warm in the cold.</p>
<p>Accompanying the body redesign were several new color options, including two-tone color schemes. The most popular color combination in 1956 was a red exterior with a red interior and wheel accents. Eye catching on the street, you&rsquo;d be able to spot the 56 car from a long way off, until it sped out of your view.</p>
<p>Most 1956 cars were equipped with two four-barrel carburetors, while the absolute base (single carb) and Special High-Lift Camshaft options were produced in lower numbers.</p>
<p>The 1956 Corvette has retained its value well over the years. Originally selling for the median income of most Americans, the 1956 Corvette sells for an average of about $46,000 in today&rsquo;s market.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1957/corvette/parts.html' => array(
                'title' => '1957 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1957 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1957 Corvette Parts" alt="1957 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1957.jpg" /></p>
<p dir="ltr"><span>The 1957 Corvette was the first Corvette available with Fuel Injection technology. Additionally, the &rsquo;57 Fuelie was the first mass-produced car to produce one horsepower per cubic inch of engine displacement. Paired with the first-ever four-speed manual transmission and racing suspension, the &rsquo;57 Corvette was a &ldquo;roaring brute when pushed hard&rdquo; and an &ldquo;absolute jewel&hellip;around town&rdquo; (</span><a href="http://www.roadandtrack.com/new-cars/reviews/a9776/1957-chevrolet-corvette-4-speed/"><span>Road and Track</span></a><span>, 1957).</span></p>
<p dir="ltr"><span>Chevrolet sold 6,339 Corvettes in 1957. GM set the Corvette&rsquo;s base price at $3,176, but a list of performance options was available, and many Corvette buyers tricked their cars out with Whitewall Tires, PosiTraction, and the Auxiliary Hardtop. In 1957, you could order a Corvette in Inca Silver, but only 65 orders were placed for this color.</span></p>
<p dir="ltr"><span>1957 saw the continued availability of dealer and owner seatbelts, but passenger safety restraints were still not installed at the factory.</span></p>
<p dir="ltr"><span>As a milestone Corvette, the &rsquo;57 Fuelie is highly desirable, and in flawless condition, sells for around $170,000.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1958/corvette/parts.html' => array(
                'title' => '1958 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1958 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1958 Corvette Parts" alt="1958 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1958.jpg" /></p>
<p dir="ltr"><span>On its fourth birthday, the Corvette lost four teeth! The 1958 Corvette saw an extensive body redesign, updated interior, and two additional headlights. Part of the extensive redesign included the removal of four teeth from the grille, which reduced the total number from thirteen to nine. The updated design included spears on the side coves, and was the only year to include louvers on the hood and spears on the trunk.</span></p>
<p dir="ltr"><span>Chevrolet designers also redesigned the Corvette instrument panel for the 1958 model year. The designers moved the instrument cluster from the center of the dash to positions centered on the steering wheel. The tachometer moved from out of the line of sight of the driver to directly underneath the speedometer, which on some cars, went all the way to 190mph.</span></p>
<p dir="ltr"><span>Chevrolet sold 9,168 Corvettes in 1958. The base model roared off the dealer lot with 230 horsepower and a three-speed manual transmission. Multiple engine options existed for this year, but the two most popular were carbureted 245 horsepower model and the fuel injected,290 horse model. In 1958, for the first time, each of Chevrolet&rsquo;s Corvettes shipped from the factory with seatbelts. This addition helped drivers stay firmly planted in the seats of their Corvette, and helped them maintain control when pushing their cars fast.</span></p>
<p><span id="docs-internal-guid-7a974ce7-be9e-6782-f5b3-c9119c4ee69e"><span>Originally selling for a base price of $3,591, a base model &rsquo;58 sells for anywhere between $32,000 and $100,000 in today&rsquo;s market. T</span><span>he most desired model of the 1958 Corvette is the 290 horsepower version, which sells for an average of $85,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1959/corvette/parts.html' => array(
                'title' => '1959 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1959 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1959 Corvette Parts" alt="1959 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1959.jpg" /></p>
<p dir="ltr"><span>Turquoise, turquoise, and more turquoise! The 1959 Corvette was the only Corvette ever produced with a soft top available in the color turquoise. Only 217 such soft tops were sold, making this version incredibly rare amidst a field of 9,670 1959 Corvettes. GM removed the hood louvers and trunk spears in 1959, but added the T-handled shifter to the four-speed, enabling a reverse lockout.</span></p>
<p dir="ltr"><span>Black interior was first offered in 1959, and while 2,062 cars shipped with black, red interior was still the most popular, with 5,124 models equipped with red carpet and seating. 1,594 cars were painted Tuxedo Black. Roman Red was a close second, and was applied to 1,542 cars. The third most-ordered color in &rsquo;59 was the gorgeous shade of blue named &ldquo;Frost Blue&rdquo;.</span></p>
<p><span id="docs-internal-guid-7a974ce7-beac-11ae-0225-1f9b7af731be"><span>The base model of the 1959 Corvette remained equipped with a 283ci carbureted engine which sent 230 horsepower to the rear wheels through a 3-speed manual transmission. The base price was $3,875, but the option sheet and list of performance enhancements was long. Today, an average 1959 Corvette sells for around $46,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1960/corvette/parts.html' => array(
                'title' => '1960 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1960 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1960 Corvette Parts" alt="1960 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1960.jpg" /></p>
<p dir="ltr"><em>&rdquo;And now, ladies and gentlemen, it is my pleasure to inform you that GM has, for the first time, produced 10,000 Corvettes in one year!&rdquo;</em></p>
<p dir="ltr"><span>Yes, you read that right. In 1960, GM produced 10,261 Corvettes. This landmark achievement happened in the same year that America&rsquo;s youngest President was elected to office, the 50 Star American Flag was unveiled, and </span><span>To Kill a Mockingbird</span><span> was released.</span></p>
<p dir="ltr"><span>Interestingly, in 1960, Chevrolet did not allow automatic transmissions to be paired with either of the available fuel-injected engines. Some of the most popular options added to 1960 Corvettes were: a heater, a signal-seeking AM radio, windshield washers, and whitewall tires.</span></p>
<p dir="ltr"><span>The most popular engine in 1960 was the base model, which still produced 230 horsepower with a four-barrel carburetor. The second most popular engine in 1960 was the dual four-barrel carbed 283, which made 270 horsepower.</span></p>
<p dir="ltr"><span>The base price for the Corvette in 1960 was $3,872: essentially the same as the previous year. On average, a base model 1960 Corvette sold today goes for around $62,000, but in mint condition, the 290 horse FI model sells for upwards of $165,000.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1961/corvette/parts.html' => array(
                'title' => '1961 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1961 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1961 Corvette Parts" alt="1961 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1961.jpg" /></p>
<p dir="ltr"><span>Say hello to the boat tail! Largely unchanged since 1958, the 1961 Corvette marked another drastic change in the Corvette design. An updated front fascia with a shrunken grille combined with a sleek rear gave the Corvette a clean, modern appearance. Also new for 1961, the taillights were arrayed in a set of four below the trunk-line, in a design that has manifested itself in every Corvette since.</span></p>
<p dir="ltr"><span>Vehicle occupants also received more space in the 1961 model due to a narrower transmission tunnel. Coupled with several courtesy features like windshield washer, courtesy light, parking brake alarm, and sun visors included in the base price, driving a base model 1961 Corvette was a very pleasant experience.</span></p>
<p dir="ltr"><span>Also new for 1961 was a relocation of the exhaust ports. In earlier models, Corvette vented exhaust through openings in the back of the body, but in 1961, designers moved the exhaust ports to right behind the rear wheels. The same powerplant as previous years was included in the base price, but the fuel injected engines both received significant performance boosts.</span></p>
<p><span id="docs-internal-guid-7a974ce7-becb-2bae-71e1-9f8874dbc45d"><span>Chevrolet set the base price of the &rsquo;61 Corvette at $3,934, and for the second year in a row, sold over 10,000 cars: 10,939 to be precise. Today, the 1961 Corvette, depending on the engine, sells for an average of anywhere between $47,000 and $88,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1962/corvette/parts.html' => array(
                'title' => '1962 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1962 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1962 Corvette Parts" alt="1962 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1962.jpg" /></p>
<p dir="ltr"><span>With a new year came a new engine. In 1962, Chevrolet installed its TurboFire 327 in the Corvette, which upped the horsepower in the base model to 250. Also new for &rsquo;62 was the unavailability of the two-tone paint schemes. Design changes to the side coves made it impossible to order Corvettes in contrasting colors.</span></p>
<p dir="ltr"><span>The grille on the &rsquo;62 car was also changed from the 1961 model. A darkened shade of gray covered the mouth of the 1962 Corvette, and was very low-profile as one gazed at the car. The heater was finally added as a standard feature in 1962.</span></p>
<p dir="ltr"><span>Another notable about the 1962 car is that it was the last Corvette until the C6 to display its headlights at all times. Secondly, The TurboFire 327 was available in four configurations. The base model generated 250 horses, but options were available for 300hp, 340hp, and a Fuel Injected model generating 360hp was also available.</span></p>
<p><span id="docs-internal-guid-7a974ce7-beef-abec-321c-261079c8a9ac"><span>Along with the drastic new design came a massive increase in Corvette sales in 1962. Chevy sold 14,531 Corvettes in 1962. The base price of a Corvette also increased to $4,038 in &rsquo;62, but the added features and performance were well worth the $104 price increase over the &rsquo;61 model. Today, the average &rsquo;62 &lsquo;Vette sells for nearly $50,000 in the base engine configuration.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5415" category_ids="5415,5416,5417,5422,5429,5443,5444,5463,5470,5471,5480,5484,5491,5492,5493,5494,5495,5496,5501,5502,5503,5504,5508,5509,5510,5511,5515,5516,5517,5518,5519,5520,5521,5522,5523,5524,5525,5526,5527,5528,5529,5530,5531,5543,5544,5545,5546,5549,5554,5555,5566,5567,5580,5581,5582,5593,5599,5605,5606,5611,5569,5612,5614,5615,5616,5620,5623,5627,5628,5629,5630,5631,5632,5633,5634,5635,5636,5639,5645,5653,5661,5665,5666,5670,5671,5672,5673,5674,5675,5681,5686,5690,5691,5704,5711,5720,5721,5726,5727,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5748,5749,5754,5772,5780,5781,5792,5793,5794,5795,5796,5797,5800,5809,5812,5817,5818,5819,5820,5824,5825,5826,5827,5828,5829,5835,5836,5837,5838,5839,5842,5849,5852,5857,5858,5859,5860,5861,5862,5870,5874,5875,5876,5881,5888,5898,5899,5900,5901,5902,5903,5904,5905,5906,5907,5908,5909,5910,5911,5917,5918"}}</p>'
            ),
            '1963/corvette/parts.html' => array(
                'title' => '1963 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1963 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1963 Corvette Parts" alt="1963 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1963.jpg" /></p>
<p dir="ltr"><span>Commonly referred to as the &ldquo;Split-Window&rdquo;, the 1963 Corvette Sting Ray is one of the most desired Corvettes ever. A product of Larry Shinoda&rsquo;s design genius, the &rsquo;63 Sting Ray retained the same basic rear section as the 1962 Corvette, but the overall dimensions were decreased throughout the entire car. Additionally, the 1963 Corvette was the first to be offered as both a coupe and a convertible, and while the Coupe sold well, the convertible was the best seller for the year. Chevy sold 10,919 convertibles in 1963, and they sold 10,594 coupes.</span></p>
<p dir="ltr"><span>The ladder-type frame and &ldquo;birdcage&rdquo; style body reinforcement added incredible rigidity to the new Corvette, without massive increases in weight. The 1963 Corvette shucked the solid rear axle in favor of a new, independent rear suspension system. </span></p>
<p dir="ltr"><span>1963 also saw the introduction of the Z06 performance package, which, in early versions, was a package designed to include everything a Corvette owner would need to race his shiny new Sting Ray. 199 L84 Sting Rays were equipped with the Z06 Package.</span></p>
<p><span id="docs-internal-guid-7a974ce7-bf02-d217-7916-42a89bb580dc"><span>Four engines were available in &rsquo;63, and the same three transmissions continued to be available. The highest output came in the form of the Fuel Injected L84, and it produced 360hp. The base Corvette Coupe retailed for $4,252, and the Convertible sold for $4,037. Today, 1963 Corvettes start at an average of $44,000, but the rarest Z06&rsquo;s can bring nearly $380,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5920" category_ids="5920,5921,5926,5934,5956,5965,5986,5989,5990,5997,5998,5999,6007,6008,6014,6021,6022,6023,6033,6037,6042,6060,6064,6065,6066,6071,6075,6076,6080,6085,6092,6093,6098,6099,6100,6107,6108,6109,6110,6111,6112,6113,6114,6115,6116,6117,6132,6133,6146,6150,6154,6165,6171,6172,6180,6181,6182,6198,6199,6200,6215,6221,6228,6229,6183,6234,6238,6239,6244,6250,6256,6257,6261,6262,6263,6264,6265,6266,6277,6278,6279,6284,6290,6291,6300,6313,6319,6320,6321,6322,6323,6324,6325,6326,6327,6328,6329,6330,6331,6332,6333,6338,6339,6348,6349,6359,6368,6377,6378,6379,6380,6389,6390,6391,6392,6394,6399,6400,6401,6423,6427,6433,6434,6435,6436,6437,6444,6447,6454,6455,6470,6476,6477,6490,6497,6501,6502,6503,6504,6505,6510,6511,6516,6526,6527,6533,6546,6547,6552,6553,6554,6555,6556,6557,6558,6559,6569,6570,6571,6572,6573,6581,6588,6599,6619,6620,6626,6627,6628,6637,6638,6645,6666,6672,6673,6674,6679,6689,6692,6707,6708,6709,6710,6722,6723,6724,6725,6726,6733,6738,6739,6740,6741,6742,6749,6755"}}</p>'
            ),
            '1964/corvette/parts.html' => array(
                'title' => '1964 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1964 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1964 Corvette Parts" alt="1964 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1964.jpg" /></p>
<p dir="ltr"><span>The 1964 Corvette was a tremendous follow-up to the successes of the first Sting Ray. GM responded to complaints and removed the divider in the rear window to increase rearward visibility. GM designers also removed the hood vent panels on the &rsquo;64 car, leaving a unique shape to the hood.</span></p>
<p dir="ltr"><span>1964 saw performance increases to the L76 and L84 engine options. In fact, the L76 engine, which generated 365 horsepower, was the first Corvette engine to utilize the Holley Carburetor design. The L84 fuelie saw a 15 horse increase over the previous year, resulting in total output of 375 hp.</span></p>
<p><span id="docs-internal-guid-7a974ce7-bf1a-d912-0f56-4ce3a01fc2fc"><span>Chevrolet saw continued success in sales of the &rsquo;64 Corvette, as they moved 22,229 cars through the factory and into the hands of smiling owners. The convertible was the strong seller that year, with 13,925 cars sold. The coupe sold 8,304 cars.</span></span></p>
<p><span><span><span id="docs-internal-guid-7a974ce7-bf2b-2588-d7cb-2097b68640f4"><span>Chevrolet retained the same pricing as the &rsquo;63 car for the 1964 model year. The Coupe retailed for $4,252, and the Convertible retailed for $4,037. In today&rsquo;s market, the average 1964 Corvette, in the base configuration, sells for around $40,000, but models with higher output are easily worth over $80,000.</span></span></span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5920" category_ids="5920,5921,5926,5934,5956,5965,5986,5989,5990,5997,5998,5999,6007,6008,6014,6021,6022,6023,6033,6037,6042,6060,6064,6065,6066,6071,6075,6076,6080,6085,6092,6093,6098,6099,6100,6107,6108,6109,6110,6111,6112,6113,6114,6115,6116,6117,6132,6133,6146,6150,6154,6165,6171,6172,6180,6181,6182,6198,6199,6200,6215,6221,6228,6229,6183,6234,6238,6239,6244,6250,6256,6257,6261,6262,6263,6264,6265,6266,6277,6278,6279,6284,6290,6291,6300,6313,6319,6320,6321,6322,6323,6324,6325,6326,6327,6328,6329,6330,6331,6332,6333,6338,6339,6348,6349,6359,6368,6377,6378,6379,6380,6389,6390,6391,6392,6394,6399,6400,6401,6423,6427,6433,6434,6435,6436,6437,6444,6447,6454,6455,6470,6476,6477,6490,6497,6501,6502,6503,6504,6505,6510,6511,6516,6526,6527,6533,6546,6547,6552,6553,6554,6555,6556,6557,6558,6559,6569,6570,6571,6572,6573,6581,6588,6599,6619,6620,6626,6627,6628,6637,6638,6645,6666,6672,6673,6674,6679,6689,6692,6707,6708,6709,6710,6722,6723,6724,6725,6726,6733,6738,6739,6740,6741,6742,6749,6755"}}</p>'
            ),
            '1965/corvette/parts.html' => array(
                'title' => '1965 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1965 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1965 Corvette Parts" alt="1965 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1965.jpg" /></p>
<p dir="ltr"><span>1965 was a big year for the Corvette, literally. In March of 1965, the Corvette Magicians at GM unveiled the L78 Turbo-Jet Engine as an option for the 1965 car. This 396 cubic inch big block produced 425 horsepower: the highest output Corvette to date. 1965 also marked the year that Corvette gained new stopping abilities in the form of disc brakes on all four wheels. </span></p>
<p dir="ltr"><span>In 1962, Duntov and his design team began working on adapting the European disc brake system to stop the heavier-weight Corvette. Although Duntov had employed </span><a href="http://www.girlingauto.com/"><span>Girling</span><span> brakes</span></a><span> on his Grand Sport racers, these cars were much lighter than the Corvette. The brakes used on the &lsquo;65 Corvette were much beefier than their European counterparts, and were very resistant to brake fade as compared to the drum brakes used on earlier Corvettes. When unveiled as standard equipment on the 1965 Corvette, the market got excited. For the first time, the Corvette came equipped with powerful brakes that could reign in the incredible horsepower produced by the Corvette engines.</span></p>
<p dir="ltr"><span>Chevrolet also updated the shape of the hood on the &lsquo;65 car by eliminating the hood depressions left over from the 1963-64 models. 1965 saw a continued increase in Corvette sales. Chevy sold a total of 23,564 1965 Corvettes. Of this, 15,378 were convertibles. The remaining 8,186 were of the coupe variant.</span></p>
<p><span id="docs-internal-guid-7a974ce7-bf29-9874-c684-2706339f313e"><span>The base prices increased slightly in 1965. Equipped with a 250 hp 327 ci, engine, and a 3 speed manual transmission, the Coupe started at $4,321. Equally equipped, the Convertible based at $4,106. In today&rsquo;s market, the average 1965 Corvette could sell for anywhere between $38,000 and $100,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5920" category_ids="5920,5921,5926,5934,5956,5965,5986,5989,5990,5997,5998,5999,6007,6008,6014,6021,6022,6023,6033,6037,6042,6060,6064,6065,6066,6071,6075,6076,6080,6085,6092,6093,6098,6099,6100,6107,6108,6109,6110,6111,6112,6113,6114,6115,6116,6117,6132,6133,6146,6150,6154,6165,6171,6172,6180,6181,6182,6198,6199,6200,6215,6221,6228,6229,6183,6234,6238,6239,6244,6250,6256,6257,6261,6262,6263,6264,6265,6266,6277,6278,6279,6284,6290,6291,6300,6313,6319,6320,6321,6322,6323,6324,6325,6326,6327,6328,6329,6330,6331,6332,6333,6338,6339,6348,6349,6359,6368,6377,6378,6379,6380,6389,6390,6391,6392,6394,6399,6400,6401,6423,6427,6433,6434,6435,6436,6437,6444,6447,6454,6455,6470,6476,6477,6490,6497,6501,6502,6503,6504,6505,6510,6511,6516,6526,6527,6533,6546,6547,6552,6553,6554,6555,6556,6557,6558,6559,6569,6570,6571,6572,6573,6581,6588,6599,6619,6620,6626,6627,6628,6637,6638,6645,6666,6672,6673,6674,6679,6689,6692,6707,6708,6709,6710,6722,6723,6724,6725,6726,6733,6738,6739,6740,6741,6742,6749,6755"}}</p>'
            ),
            '1966/corvette/parts.html' => array(
                'title' => '1966 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1966 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1966 Corvette Parts" alt="1966 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1966.jpg" /></p>
<p dir="ltr"><span>In 1966, Chevrolet decided that the 396 L78 engine was just too small. Instead, they installed a 427ci engine, offered it with two different horsepower ratings, and sold over 10,000 Corvettes with the 427 alone. Today, conservative estimates believe that each of these engines produced over 400 horsepower. </span></p>
<p dir="ltr"><span>Unique to the mid-year Corvettes was the ability to have a car that truly fit ones lifestyle. Arguably, for the first time in 1966, every driver could be satisfied with a Corvette. On one end of the spectrum was the base model, which was powered by the standard 327ci, and only had 300 horsepower.With an automatic transmission, it was easy to just get in the car and drive. But, for the true thrill-seekers, the big-block 427ci offered heart-stopping power (approximately 450 hp) at the touch of a pedal. </span></p>
<p dir="ltr"><span>With all these added performance features came a renewed focus on occupant safety. In 1958, seat belts were made standard on the Corvette, and in 1966, shoulder harnesses were added to the factory equipment. </span></p>
<p><span id="docs-internal-guid-7a974ce7-bf3c-255d-3878-f21198b8d656"><span>Out of all the mid-year Corvettes, the 1966 was the top seller by about 5,000 cars. Chevrolet sold 27,720 cars in 1966. Of these, only 9,958 were coupes. The remaining 17,762 were convertibles in one of the ten available colors. Outfitted with the high-output L72 427ci and a 4 speed, a coupe could be had for $4,792.20. Today, this same car is worth around $85,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5920" category_ids="5920,5921,5926,5934,5956,5965,5986,5989,5990,5997,5998,5999,6007,6008,6014,6021,6022,6023,6033,6037,6042,6060,6064,6065,6066,6071,6075,6076,6080,6085,6092,6093,6098,6099,6100,6107,6108,6109,6110,6111,6112,6113,6114,6115,6116,6117,6132,6133,6146,6150,6154,6165,6171,6172,6180,6181,6182,6198,6199,6200,6215,6221,6228,6229,6183,6234,6238,6239,6244,6250,6256,6257,6261,6262,6263,6264,6265,6266,6277,6278,6279,6284,6290,6291,6300,6313,6319,6320,6321,6322,6323,6324,6325,6326,6327,6328,6329,6330,6331,6332,6333,6338,6339,6348,6349,6359,6368,6377,6378,6379,6380,6389,6390,6391,6392,6394,6399,6400,6401,6423,6427,6433,6434,6435,6436,6437,6444,6447,6454,6455,6470,6476,6477,6490,6497,6501,6502,6503,6504,6505,6510,6511,6516,6526,6527,6533,6546,6547,6552,6553,6554,6555,6556,6557,6558,6559,6569,6570,6571,6572,6573,6581,6588,6599,6619,6620,6626,6627,6628,6637,6638,6645,6666,6672,6673,6674,6679,6689,6692,6707,6708,6709,6710,6722,6723,6724,6725,6726,6733,6738,6739,6740,6741,6742,6749,6755"}}</p>'
            ),
            '1967/corvette/parts.html' => array(
                'title' => '1967 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1967 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1967 Corvette Parts" alt="1967 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1967.jpg" /></p>
<p dir="ltr"><span>If I were to say that twenty is the most important number to the 1967 Corvette, what would your first thought be? Perhaps the size of the gas tank? Well, yes, the standard fuel capacity on a mid-year Corvette is 20 gallons, but over 100,000 cars came equipped with this fuel tank. Actually, the number twenty relates to how many customers chose to order the L88 engine in their 1967 Corvette. At the time, Chevy advertised this engine as producing 430 hp, but actual output was probably closer to 500 hp. </span></p>
<p dir="ltr"><span>To accommodate for the big-block engines, the hood on the last mid-year car was redesigned to incorporate a scoop. This scoop was merely for decoration on all except the 20 L88 cars. The GM design team decided that 1967 was the year to streamline the Corvette. Exterior accoutrements were removed, and due to Federal regulations, so were knock-off wheels. In their place, GM installed aluminum rally wheels. Because of their composition, these wheels saved weight, and their design allowed for more airflow to the brakes, thereby providing better cooling.</span></p>
<p dir="ltr"><span>Remember those 20 L88 cars that I mentioned? Between being the last of the mid-year Sting Ray, and the fact only 20 out of the 22,940 cars sold were equipped with this package, it&rsquo;s not at all surprisingly that a &lsquo;67 L88 is a very valuable car. In today&rsquo;s market, a &lsquo;67 L88 is worth over 2 million dollars. A lower horsepower &lsquo;67 is still worth anywhere between $50,000 and $300,000. </span></p>
<p dir="ltr"><span>As the last of the mid-years, there&rsquo;s no doubt that the &lsquo;67 Corvette is a special car. Easily spotted by its flat hood, absence of decals, and rally wheels, 1967 Corvettes are a treasure to see at shows or on the street. </span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/5920" category_ids="5920,5921,5926,5934,5956,5965,5986,5989,5990,5997,5998,5999,6007,6008,6014,6021,6022,6023,6033,6037,6042,6060,6064,6065,6066,6071,6075,6076,6080,6085,6092,6093,6098,6099,6100,6107,6108,6109,6110,6111,6112,6113,6114,6115,6116,6117,6132,6133,6146,6150,6154,6165,6171,6172,6180,6181,6182,6198,6199,6200,6215,6221,6228,6229,6183,6234,6238,6239,6244,6250,6256,6257,6261,6262,6263,6264,6265,6266,6277,6278,6279,6284,6290,6291,6300,6313,6319,6320,6321,6322,6323,6324,6325,6326,6327,6328,6329,6330,6331,6332,6333,6338,6339,6348,6349,6359,6368,6377,6378,6379,6380,6389,6390,6391,6392,6394,6399,6400,6401,6423,6427,6433,6434,6435,6436,6437,6444,6447,6454,6455,6470,6476,6477,6490,6497,6501,6502,6503,6504,6505,6510,6511,6516,6526,6527,6533,6546,6547,6552,6553,6554,6555,6556,6557,6558,6559,6569,6570,6571,6572,6573,6581,6588,6599,6619,6620,6626,6627,6628,6637,6638,6645,6666,6672,6673,6674,6679,6689,6692,6707,6708,6709,6710,6722,6723,6724,6725,6726,6733,6738,6739,6740,6741,6742,6749,6755"}}</p>'
            ),
            '1968/corvette/parts.html' => array(
                'title' => '1968 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1968 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1968 Corvette Parts" alt="1968 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1968.jpg" /></p>
<p dir="ltr"><span>Shark attack! In the 1968 model year, GM introduced the C3 Corvette with its shark-like body shape. Surprisingly, even though the 1968 body was a drastic change over previous years, each of the 28,566 cars sold was built on essentially the same chassis as the C2 &ldquo;mid-year&rdquo; Corvettes and outsold all preceding years of the Corvette. &nbsp;</span></p>
<p dir="ltr"><span>As the first year for the C3 generation, 1968 was something of an odd year. There were several features of the 1968 car that weren&rsquo;t shared by other cars in the C3 era. The door opened via a button and a spring-loaded plate, and the wheels were all seven inches wide. 1968 was also the last year that Chevrolet installed its 327ci engine in Corvettes. </span></p>
<p><span id="docs-internal-guid-7a974ce7-bf93-8096-2e2c-d4900cc29311"><span>The 1968 Corvette also introduced several new Corvette features. First, t-tops were first available on 1968 coupes, and would continue to be available through the remainder of the C3 era. Chevrolet has continued to offer removable roof panels on nearly every coupe since 1968. The headlights on the C3 were truly of the &ldquo;pop-up&rdquo; variety, and were seen on Corvettes until the C6 debut. Third, the Turbo Hydra-Matic transmission was introduced. This three-speed automatic was a more modern pairing with the various engines available in the Corvette, and performed well in a variety of applications.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1969/corvette/parts.html' => array(
                'title' => '1969 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1969 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1969 Corvette Parts" alt="1969 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1969.jpg" /></p>
<p dir="ltr"><span>Apparently even sharks are susceptible to strikes, because for four months during production of the 1969 Corvette, auto workers went on strike. In spite of this, GM still sold 38,762 Corvettes for the 1969 model year. The &lsquo;69 did away with the troublesome door-opening mechanism found on the &lsquo;68 car, and introduced several other new features to the Corvette. </span></p>
<p dir="ltr"><span>Most notable among these introductions is definitely the introduction of the Chevy Small-Block 350 as the standard engine for the Corvette. In the base configuration, the new engine produced 300 horsepower, but 7 other engine options were available. The all-aluminum ZL1 was rated for 430 hp, but many estimate it actually generated over 500 horses.</span></p>
<p dir="ltr"><span>For the first time in 1969, the Coupe outsold the Convertible by about 5,500 cars. Perhaps Corvette buyers preferred the more weather resistant T-Top design over the convertible, or perhaps the coupe was just more appealing to the eye. For whatever reason, Coupes outsold Convertibles in every year until 2003. </span></p>
<p><span id="docs-internal-guid-7a974ce7-bfa8-43bd-07a5-560079f61a66"><span>The maximum price of a 1969 Corvette was over $10,000, and only two buyers decided that the ZL1&rsquo;s incredible performance warranted such cost. Standard Corvettes started in the mid $4,000 range, and today, &lsquo;69 &lsquo;Vettes range from $20,000 to $90,000. Models with the L88 easily bring over half a million dollars. </span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1970/corvette/parts.html' => array(
                'title' => '1970 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1970 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1970 Corvette Parts" alt="1970 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1970.jpg" /></p>
<p dir="ltr"><span>Although the lowest production year since 1962, the 1970 Corvette received several enhancements to its base configuration. First, Chevrolet made the Posi-Traction axle a standard feature, which improved acceleration and helped the Corvette maintain traction on slippery surfaces. Second, Chevrolet finally shirked the 3 speed manual and instead offered a wide-ratio 4 speed as the standard gearbox. Third, Chevrolet included tinted windows in the base price of the 1970 car. </span></p>
<p dir="ltr"><span>Also new for 1970 was the introduction of the 454ci big-block to the Corvette lineup. From the factory, the LS5 engine produced 390 hp. The LT1 engine option was first made available in 1970, and with its solid lifters, produced 370 hp. </span></p>
<p dir="ltr"><span>Total production for 1970 was 17,316. Of this, only 6,648 were convertibles. The remaining 10,668 cars were of the coupe variety. As &ldquo;The Last of the Mohicans&rdquo;, the 1970 Corvette was truly a warrior car which charged on in the face of looming government regulations. Over the next few years, emissions and safety regulations stripped the Corvette of its chrome and forced it to swallow its horsepower. </span></p>
<p><span id="docs-internal-guid-7a974ce7-bfc2-ee4f-a644-e6dae5fb697d"><span>Not many Corvettes are left from 1970, and those that are tend to sell for around $30,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1971/corvette/parts.html' => array(
                'title' => '1971 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1971 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1971 Corvette Parts" alt="1971 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1971.jpg" /></p>
<p dir="ltr"><span>Did you want to switch your transmission for free? Well, if you ordered a Corvette in 1971, you could choose to replace your 4 speed manual with the Turbo Hydra-Matic 3 speed automatic at no additional charge. </span></p>
<p dir="ltr"><span>In preparation for new fuel &nbsp;and efficiency requirements, 1971 engines were all detuned in order to operate on lower octane fuels. The base engine was still a small-block 350ci, but it&rsquo;s power output was reduced to 270 hp. The LS6, a 425 hp 454ci, was available, but less than 200 were ordered, and the cost of the engine itself was over $1,200.</span></p>
<p dir="ltr"><span>Two colors were unique to the 1971 Corvette: Nevada Silver and Brands Hatch Green. Even though it was only available in 1971, Brands Hatch Green was the second most popular color in &lsquo;71. War Bonnet Yellow was the most popular color. </span></p>
<p><span id="docs-internal-guid-7a974ce7-bfd1-1538-fca1-91ead2f40c64"><span>With the exception of cars with the LS6 engine, &lsquo;71 Corvettes sell for an average of $20,000-$30,000. 21,801 cars were produced, and Chevrolet sold twice as many coupes as it did Convertibles.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1972/corvette/parts.html' => array(
                'title' => '1972 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1972 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1972 Corvette Parts" alt="1972 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1972.jpg" /></p>
<p dir="ltr"><span>It&rsquo;s time to say goodbye to the era of the high-output muscle car. Emissions regulations forced GM engineers to further detune the Corvette, which left only 200 hp in the 350ci base engine. In 1972, the most powerful Corvette engine available was the 270 hp LS5. The LT1, which produced 255 hp in &lsquo;72, wasn&rsquo;t offered again until the 1990&rsquo;s. </span></p>
<p dir="ltr"><span>Other notable &ldquo;lasts&rdquo; about the &lsquo;72 car are that no car after it had chrome bumpers on both the front and rear. The removable rear window (coupe) was no longer available after &lsquo;72, and after 1972, the windshield wipers no longer hid under a vacuum operated panel. </span></p>
<p dir="ltr"><span>Overall, the external changes to the &lsquo;72 car were minimal. Like in preceding years, a shade of green was the top selling color, and the coupe outsold the convertible by about 14,000 cars. </span></p>
<p dir="ltr"><span>Now, you might be curious as to why horsepower numbers are so low for 1972, compared to &lsquo;71. Up until 1972, GM published the </span><span>gross horsepower</span><span> ratings for its engines, but in 1972, it began publishing the </span><span>net horsepower</span><span> instead, Net horsepower only measures the amount of horsepower sent to the rear wheels through the driveline, while gross horsepower measures the absolute amount of power the engine produces when tested in a closed system.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1973/corvette/parts.html' => array(
                'title' => '1973 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1973 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1973 Corvette Parts" alt="1973 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1973.jpg" /></p>
<p dir="ltr"><span>So, you&rsquo;ve been test driving the </span><span>Shark</span><span> for a few years, but there&rsquo;s just too much road noise inside the cockpit for your taste. Well, 1973 is your year! Chevrolet claimed a 40% reduction in cabin noise, due to the installation of sound deadening material on several inner panels and a layer of deadening on the inside of the hood. </span></p>
<p dir="ltr"><span>The 1973 Corvette also took off it&rsquo;s Chrome teeth and pranced out of the factory wearing a urethane front bumper in order to comply with Federal Highway Safety regulations. Radial tires were first introduced to Corvette in this year, and occupants received greater side-impact protection due to reinforced doors. </span></p>
<p dir="ltr"><span>1973 saw a few departures from previous years. First, the rear window was no longer removable, and windshield wipers were mounted externally instead in the hidden panel. The grille lost it&rsquo;s shiny egg-crate grille, and instead looked much more demure. </span></p>
<p><span id="docs-internal-guid-7a974ce7-bff9-5dec-b722-3936ad8b96bc"><span>30,464 cars rolled off the GM assembly line in 1973. Convertible sales continued their decline: less than 5,000 were sold. There&rsquo;s little demand for &lsquo;73 Corvettes and they can often be purchased for less than $25,000. This is only about five times the original base price of a little over $5,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1974/corvette/parts.html' => array(
                'title' => '1974 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1974 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1974 Corvette Parts" alt="1974 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1974.jpg" /></p>
<p dir="ltr"><span>1974 was another year of &ldquo;lasts&rdquo; for the Corvette. &lsquo;74 was the last car not to have catalytic converters, and was the last Corvette offered with true dual exhausts. In fact, in 1974, each muffler had a resonator built in to help improve the exhaust note. The &ldquo;big block&rdquo; engine was also last offered in the 1974 year model.The LS4 used 454ci to produce 270 hp. All high-displacement Corvette engines since 1974 have been built on the &ldquo;small-block&rdquo; design.</span></p>
<p dir="ltr"><span>The &lsquo;74 is also known as the Corvette with the split bumper. It was the second year of the urethane bumpers, and in the 1974 design, a seam at the middle of the front bumper joined the two halves together at the license plate. 1974 was the only year to employ the &ldquo;split bumper&rdquo; design. Subsequent years used one piece bumpers. </span></p>
<p><span id="docs-internal-guid-7a974ce7-c007-9162-badc-334de04c7ee7"><span>Chevrolet sold 37,502 Corvettes in 1974, but the Convertible only made up 5,474 of the total Corvettes sold. Base price crept up to about $6,000, and as evidenced by their low value in the current market ($12,000-$17,000), this model is not in high demand.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1975/corvette/parts.html' => array(
                'title' => '1975 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1975 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1975 Corvette Parts" alt="1975 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1975.jpg" /></p>
<p dir="ltr"><span>1975 was the first year since 1964 in which GM only offered one engine displacement for the Corvette. In 1975, the small block 350ci came in two configurations. The base configuration produced 165 hp, while the L82 produced 205 hp. The Turbo Hydra-Matic was installed installed in 28,473 cars, and the remaining 9,992 buyers opted for the 4 speed manual in either wide or close gear ratios. A new , no-points distributor and High Energy Ignition were added, and the tachometer went from being distributor driven to electronically driven.</span></p>
<p dir="ltr"><span>1975 also saw the installation of the Corvette&rsquo;s first catalytic converter. In order to comply with new EPA regulations on exhaust emissions, a single Catalytic converter cleaned the exhaust, and then expelled in through two exit pipes.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c010-6aae-6041-36f9a3cf79de"><span>Also unique about the 1975 car is its convertible. In July of 1975, in response to Federal Safety regulations, Chevrolet built the last C3 Convertible. Although the C4 eventually brought the drop-top &lsquo;Vette back, 1975 was the last year that a Corvette convertible sold for less than its coupe counterpart. In 1975, these cars sold for $6,550.10, and $6,810.10, respectively.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1976/corvette/parts.html' => array(
                'title' => '1976 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1976 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1976 Corvette Parts" alt="1976 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1976.jpg" /></p>
<p dir="ltr"><span>Ten years earlier, the top selling Corvette color was green. Oh, how the times change. In 1976, Green was the poorest selling color for the Corvette. Instead, white was the most popular color, and it was painted on over 10,000 cars. Silver, red, brown, and orange were other popular colors. </span></p>
<p dir="ltr"><span>Prior to 1976, the air induction system was located at the rear of the hood. This made a noise which occupants could hear inside the vehicle. In &lsquo;76, designers moved the air induction system to over the radiator, but left the outer design of the hood the same as the previous year. The hood design was changed in 1977, which means that the hood on the &lsquo;76 car is unique amongst C3 cars.</span></p>
<p dir="ltr"><span>46,558 Corvettes were sold during the 1976 model year. For the first time in thirteen years, only one body configuration was offered, the coupe. With its &ldquo;sport&rdquo; steering wheel (new for &lsquo;76), the Corvette sold for a base price of $7,604.85. All customers optioned power brakes, and all but 173 cars were sold with power steering. Base engine output increased by 15 horsepower (to 180 hp), and the L82 produced 210 hp.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1977/corvette/parts.html' => array(
                'title' => '1977 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1977 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1977 Corvette Parts" alt="1977 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1977.jpg" /></p>
<p dir="ltr"><span>If you were one of the 41,231 people who ordered an automatic transmission on your 1977 Corvette, then you also had the option, for the first time, and for only $88, to add cruise control to your car. Almost 30,000 cars came so equipped, and with premium leather seats standard equipment in 1977, the Corvette was a fantastic gran touring car for the American market.</span></p>
<p dir="ltr"><span>Powertrain options were the same for &lsquo;77, but the manufacturer changed to blue engine paint in the middle of the model year. As such, early production cars have orange engine paint, while later cars have blue paint. The &lsquo;77 car also included power brakes and steering as standard equipment. </span></p>
<p><span id="docs-internal-guid-7a974ce7-c048-4610-79d7-1c1ed16a6952"><span>Power windows and air conditioning were ordered on most cars, and although the base price was $8,647.65, by the time most people added their convenience options, the car sold for over $10,00. 49,213 cars were produced, and in todays market, the average &lsquo;77 Corvette sells for around $10,000.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1978/corvette/parts.html' => array(
                'title' => '1978 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1978 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1978 Corvette Parts" alt="1978 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1978.jpg" /></p>
<p dir="ltr"><span>In 1978, the Corvette turned 25. To celebrate, it was the pace car for the </span><span>Indy 500</span><span>. A redesign also rolled into the showroom. The fastback rear end included a larger rear window, more cargo space, and a privacy shade to help conceal cargo from view. To match its new exterior, the Corvette&rsquo;s interior received an update too. The speedometer and tachometer were reshaped to be more square, and the windshield wiper controls were moved to the instrument panel. </span></p>
<p dir="ltr"><span>In celebration of the Corvette serving as the </span><span>Indy 500 Pace Car</span><span>, Chevrolet offered a limited edition replica of the pace car. This car featured glass roof panels, power locks and windows, A/C, 8 track stereo with upgraded speakers, and aluminum wheels. This car was available for $13,653.21, and 6,502 were sold.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c050-a18c-76da-caf21280aefd"><span>In the base configuration ($9,351.89), Chevy offered a 185 hp 350ci engine, and paired it to their wide-ratio 4 speed manual. However, most customers optioned for the automatic transmission at no additional cost. Some common options for the year were the tilt-telescoping steering column, power windows, A/C, and sport mirrors. The most common paint color was Silver Anniversary, which was a $400 option and was ordered 15,283 times.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1979/corvette/parts.html' => array(
                'title' => '1979 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1979 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1979Corvette Parts" alt="1979Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1979.jpg" /></p>
<p dir="ltr"><span>Do you know what the all-time record for Corvettes sold in a year is? Well the 1979 Corvette sure does! GM sold 53,807 Corvettes in 1979, and hasn&rsquo;t sold that many in any single year since. Approximately 12,000 cars shipped with a manual transmission, while the remaining 41,454 cars were sold with the automatic gearbox. The 1979 Corvette was also the first Corvette to include a radio in its base configuration. Buyers could add stereo, cassette, 8-track, and CB capabilities if they desired. </span></p>
<p dir="ltr"><span>Remember the &lsquo;78 </span><span>Indy Pace Car</span><span>? Well it was so popular, that in &lsquo;79, GM designers incorporated elements from this design in the regular production Corvette. The front and rear spoilers first developed for the pace car became options for 1979 Corvettes (RPO D80), the glass roof panels continued to be offered (CC1), and the &ldquo;high-back&rdquo; seats from the pace car were made standard on the &lsquo;79 car. </span></p>
<p dir="ltr"><span>The base Corvette in 1979 sold for $10,220.23. The car was available with the base 350ci, which produced 195 hp, or the optional L82, which generated 225 hp. A/C, the tilt-telescopic steering column, and the convenience group were the three most popular options. In stark contrast to previous years, where white and silver were top sellers, the color black was painted on over 10,000 1979 Corvettes.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1980/corvette/parts.html' => array(
                'title' => '1980 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1980 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1980 Corvette Parts" alt="1980 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1980.jpg" /></p>
<p dir="ltr"><span>Up until the 1980 model year, the Corvette was a fairly bare-bones car. I had basic features, but it wasn&rsquo;t a car designed for the daily commute. Features like air conditioning and power windows were available, but not standards. As the C3 generation progressed, GM offered more features as standard on the Corvette. In 1980, the manufacturer included power windows, A/C, and the tilt-telescopic steering wheel on the Corvette as standard features. Power door locks (RPO AU3) and the rear window defogger (RPO C49) were still optional. Chevrolet included three different options for the interior on the Corvette. Leather was standard, while cloth and vinyl were no-cost options.</span></p>
<p dir="ltr"><span>Also new for 1980 was the addition of bumpers with integrated spoilers. Radiator air-flow increased by 50%, and the drag coefficient decreased to .443. This gave the car an elongated appearance. This appearance heralded the soon to come C4, and was a forerunner to modern designs.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c3b2-9b28-b453-51c11c188a1d"><span>The 1980 &lsquo;Vette saved weight by employing lower density and less thick materials at strategic points throughout the car, and GM saved some paint when it installed 85 MPH speedometers in all 1980 Corvettes. The 1980 car was initially priced at $13,140.24, but by the end of the model year, its sticker price rose to $14,345.24.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1981/corvette/parts.html' => array(
                'title' => '1981 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1981 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1981 Corvette Parts" alt="1981 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1981.jpg" /></p>
<p dir="ltr"><span>GM started building Corvettes in St. Louis in 1954. Twenty-seven years later, it moved Corvette production to its new home in Bowling Green, KY. The first Corvette built in Bowling Green was built on June 1,1981. Production ceased in St. Louis later that summer on August 1. Two-tone paint was an option on the &lsquo;81 car, and while a few two-tone cars were painted in St. Louis, most were built at the Bowling Green plant.</span></p>
<p dir="ltr"><span>Chevrolet installed its computer command control module on all &lsquo;81 Corvettes, and only offered one engine - a 190 hp 350ci. Manual and automatic transmissions were both available, but the manual was unique. It was the last true four-speed ever installed in a Corvette. Although a &ldquo;4-speed manual&rdquo; was offered on the 1984 car, it contained overdrives in the top three gears.</span></p>
<p dir="ltr"><span>The power driver seat was the most popular new option on the &lsquo;81 car, but the power locks and glass roof panels were also strong sellers. Chevy sold 40,606 Corvettes in 1981. Of these, only 5,352 had two-tone paint. In base form, with either a manual or an automatic, the Corvette sold for $16,258.52.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1982/corvette/parts.html' => array(
                'title' => '1982 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1982 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1982 Corvette Parts" alt="1982 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1982.jpg" /></p>
<p dir="ltr"><span>The 1982 Corvette marked the end of an era. The C3 Corvette was produced on a massive scale, and to celebrate the success of the C3, Chevrolet offered a &ldquo;Collector&rsquo;s Edition&rdquo; car for the 1982 model year. The Collector&rsquo;s Edition sold for $22,537.59, and was painted a special &ldquo;silver-beige&rdquo; color. It was outfitted with a special hood, and its aluminum wheels were inspired by the knock-off wheels installed on some C2 Corvettes. </span></p>
<p dir="ltr"><span>Also unique to the 1982 Collector&rsquo;s Edition was the hatchback style rear window. The hatchback design offered direct access to the rear storage compartment. On other C3 models, the rear storage area could only be accessed from inside the driver&rsquo;s compartment. The Collector&rsquo;s Edition was an unlimited production model, and 6,759 cars were ordered in this configuration.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c3d0-920a-00c5-f09cab0b3d97"><span>The &lsquo;82 Corvette was only available with one engine/transmission option. The CrossFire Fuel Injection 350ci produced 200 horsepower, and was mated to a four-speed automatic transmission. The base Corvette was available for $18, 290.07, and 18,648 cars were produced. Nearly all cars were sold with cruise control, but 1,094 cars did sell without it. Total production for 1982 was 25,407.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/6763" category_ids="6763,6764,6774,6800,6808,6824,6827,6828,6840,6841,6849,6850,6867,6887,6891,6868,6869,6870,6881,6889,6882,6890,6892,6897,6903,6904,6905,6906,6907,6910,6914,6915,6916,6920,6926,6932,6937,6946,6947,6948,6949,6950,6951,6952,6953,6959,6960,6961,6962,6963,6964,6965,6966,6967,8503,6968,6969,6970,6971,6972,6973,6974,6997,6998,6999,7008,7013,7023,7029,7030,7041,7042,7057,7058,7059,7067,7073,7080,7081,7044,7097,7101,7102,7103,7108,7113,7119,7120,7127,7131,7136,7143,7144,7147,7149,7150,7158,7183,7193,7201,7202,7205,7206,7207,7208,7211,7220,7221,7222,7223,7224,7225,7226,7227,7233,7238,7248,7249,7259,7267,7274,7285,8630,7287,7302,7303,7304,7305,7306,7311,7312,7313,7335,7336,7337,7338,7343,7344,7348,7352,7353,7354,7355,7356,7357,7358,7368,7369,7384,7391,7392,7402,7406,7407,7408,7409,7410,7411,7412,7416,7423,7424,7425,7430,7440,7441,7445,7446,7447,7448,7449,7450,7451,7452,7453,7465,7466,7467,7468,7469,7470,7478,7487,7488,7511,7526,7527,7531,7535,7536,7537,7538,7551,7552,7560,7585,7559,7591,7592,7593,7603,7606,7616,7617,7618,7619,7631,7632,7633,7640,7641,7642,7648,7649,7653,7654,7659,7663,7666"}}</p>'
            ),
            '1984/corvette/parts.html' => array(
                'title' => '1984 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1984 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1984 Corvette Parts" alt="1984 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1984.jpg" /></p>
<p dir="ltr"><span>At the time, the 1984 Chevrolet Corvette outperformed the Porsche 928 on the Car and Driver skidpad. The Corvette showed .9g of lateral acceleration, while it&rsquo;s European counterparts only recorded .82g. How did Chevrolet do this? Well, in the late seventies, GM&rsquo;s engineering team decided that the Corvette needed to be built differently, so GM developed it&rsquo;s &ldquo;uniframe&rdquo;. None of the exterior body panels contributed to the actual structure of the vehicle, so the new design wasn&rsquo;t a true unibody. Instead, the perimeter frame was connected to the door posts, windshield, and floor pan to give the design its stability.</span></p>
<p dir="ltr"><span>The C4 Corvette was the product of countless hours of labor, and was an engineering masterpiece. The C4 Corvette used plastics in its rear bumper and its body panels instead of the fiberglass found on earlier models, and all exterior fiberglass seams were covered by rub molding installed around the car. The brakes included aluminum components, and even though the &lsquo;84 car had more ground clearance, the center of gravity was lower than the Corvette from the 1982 model year. Referred to as the &ldquo;spaceship&rdquo; by some enthusiasts, the 1984 Corvette utilized no analog instruments. The entire dash made use of LCD technology, and even the tachometer was a digital readout.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c3d9-a96b-94a9-2eb32f2bd0fc"><span>The &lsquo;84 Corvette shipped with 205 hp and a four-speed automatic. A manual transmission with automatic overdrive was available as a no-cost option, but only 6,443 cars were ordered with this transmission. Every car shipped with 16&rdquo; wheels (RPO QZD), and the base price of $22,361,20 reflected this. With 51,547 coupes sold during 18 months of production, the &lsquo;84 Corvette was the second most sold Corvette in GM history.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7837,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7818,7779,7783,7784,7793,7798,7802,7803,7822,7806,7808,7814,7792,7815,7787,7816,7817,7823,7824,7828,8654,7831,7829,9017,7833,7835,7836,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1985/corvette/parts.html' => array(
                'title' => '1985 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1985 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1985 Corvette Parts" alt="1985 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1985.jpg" /></p>
<p dir="ltr"><span>Even though the 1984 Corvette was said to be the best handling Corvette ever, many drivers complained that the ride was too harsh, so in 1985, GM softened the suspension and beefed up the sway bars. The driver received his softer ride, but he was still able to toss his car around a track with minimal body roll. To further improve performance, the 1985 Corvette shipped with a plastic roof panel instead of fiberglass. </span></p>
<p dir="ltr"><span>The Corvette of 1982 and 1984 was equipped the the Crossfire throttle-body injection system. Much-maligned, it was replaced in 1985 with a tuned-port injection system built by Bosch. The new L98 engine generated 230 hp and 330 lb.-ft. of torque. These performance gains were accompanied by an 11% increase in fuel economy, although the EPA rating remained the same due to the testing system. More significant than the fuel economy increase was the Corvette&rsquo;s new status as the fastest production car in America. </span><span>Car and Driver</span><span> reported that the Corvette exceeded all expectations by easily achieving speeds of 150 mph - something no other American car did in 1985.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c3e4-c07b-c039-3a8bfc5e9c3c"><span>Buyers of the 1985 Corvette could choose either the manual or automatic transmission, but approximately 30,100 cars were shipped with the automatic. Cloth seats and AM/FM stereo were standard equipment, but leather and sport seats were also available. The 1985 Corvette, with its base retail price of $24,403, was also the first American car to utilize a plastic brake booster.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1986/corvette/parts.html' => array(
                'title' => '1986 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1986 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1986 Corvette Parts" alt="1986 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1986.jpg" /></p>
<p dir="ltr"><span>In 1975, due to Federal safety regulations, Chevrolet discontinued the Corvette Convertible. At the time, consumers were unsure if a ragtop &lsquo;Vette would ever be made again. Well, in 1986, Chevrolet put those fears to bed by releasing both Coupe and Convertible versions. The Convertible was re-engineered to handle the stresses of the high-performance engines in the Corvette, and the Convertible weighed the same as its hard-top counterpart. </span></p>
<p dir="ltr"><span>Several groundbreaking new features were added for 1986. Anti-Lock brakes (ABS), which were adapted from the Bosch design, used an ECU to manage brake line pressure in order to eliminate wheel lock. The Chevrolet Vehicle Anti-Theft System (VATS), helped prevent auto-theft by requiring a special key be used. This key produced a specific amount of electrical resistance which the ignition lock cylinder measured. If the key did not produce the amount needed by the car, then the car would be disabled until detection of the proper amount of resistance. Corvettes were programmed to need one of fifteen different levels of electrical resistance.</span></p>
<p dir="ltr"><span>35,109 Corvettes were sold for the &lsquo;86 model year. Of these, 7,315 came with the cloth top, and sold for a base price of $32,032. The base price for the coupe was $27,027. Early production models were equipped with iron cylinder heads, and produced 230hp, but partway through production, GM switched to aluminum heads. These produced 235hp and were installed on all &lsquo;86 convertibles.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1987/corvette/parts.html' => array(
                'title' => '1987 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1987 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1987 Corvette Parts" alt="1987 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1987.jpg" /></p>
<p dir="ltr"><span>For a mere $20,000 option (RPO B2K), buyers of the 1987 Corvette could have their car shipped from the factory to Callaway engineering, where Callaway would fit the Corvette with twin-turbos, and would make all the other necessary adjustments to help the &lsquo;87 achieve 345 hp and 465 lb.-ft. of torque. All 188 of these cars achieved a top speed of 177.9 mph, and 65 of these were convertibles. </span></p>
<p dir="ltr"><span>Chevrolet offered three different suspension configurations for the &lsquo;87 Corvette. The standard suspension was best tuned for daily driving, and had a standard steering ratio. The new Z52 &ldquo;sport handling&rdquo; package was a hybrid between the Z51 and the base packages. The Z52 included Bilstein shocks, an engine oil cooler and HD radiator, and the structural enhancements which were standard for the convertible were installed on the coupe as a part of the Z52 package. The Z51 package was only available on the coupe, and was titled the &ldquo;Performance Handling Package&rdquo;. A fast steering ratio, all the features of the Z52 package, and a much stiffer ride were the primary characteristics of the Z51.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c400-d50a-ad3c-ab11ab56f6b0"><span>The Convertible sold well in 1987, and made up just over one-third of sales for the year. Total sales for the year reached 30,632. 20,007 were coupes, which had a base price of $27,999. Popular options were power door locks and seats, cruise control, and the premium stereo system.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1988/corvette/parts.html' => array(
                'title' => '1988 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1988 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1988 Corvette Parts" alt="1988 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1988.jpg" /></p>
<p dir="ltr"><span>The Corvette&rsquo;s thirty-fifth birthday was marked by a special anniversary edition (RPO Z01). 2,050 coupes were painted white, with black roof panels. Special emblems, white interior, and special interior trim set the special edition cars apart from their standard counterparts.</span></p>
<p dir="ltr"><span>The 1988 Corvette was the first Corvette with 17 inch wheels. These were available as part of the Z51 and Z52 packages. The Corvette braking system was also revised in 1988, with dual-piston front brakes, and a parking brake which engaged the rear pads instead of a small drum brake.The front suspension was totally redesigned, and the new zero-scrub radius greatly improved the handling characteristics of the Corvette. The Z52 Sport Handling Package was installed on 16,017 out of the total 22,789 Corvettes produced for the 1988 model year, but only 4,282 cars were equipped with the final edition of the Doug Nash &ldquo;4+3&rdquo; manual transmission. </span></p>
<p><span id="docs-internal-guid-7a974ce7-c40e-2320-d0c5-6424bf18e743"><span>The &lsquo;88 Corvette only saw 125 owners opt for the B2K Callaway Twin Turbo option, which, for on $25,895 on top of the $29,489 (coupe) base price, gave Corvette owners 382 hp and 562 lb.-ft. of torque. </span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1989/corvette/parts.html' => array(
                'title' => '1989 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1989 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1989 Corvette Parts" alt="1989 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1989.jpg" /></p>
<p dir="ltr"><span>1989 was the year of the first text message. It was also the year that the Corvette adopted the six-speed manual transmission. The transmission was a collaboration between the German firm ZF and Chevrolet. Its computer-aided gear selection (CAGS) helped the Corvette meet EPA standard for fuel economy by electronically disabling intermediate gears under certain driving conditions. </span></p>
<p dir="ltr"><span>In previous years, the Corvette had been available in three different suspension trims, but in 1989, introduced RPO FX3 Selective Ride and Handling, which, when optioned with the Z51 Performance Handling package, allowed the Corvette owner to choose one of three suspension presets to customize his ride.</span></p>
<p dir="ltr"><span>The &lsquo;89 Corvette was the first Corvette produced in fourteen years which was available with a removable hardtop.The &lsquo;89 &nbsp;convertible, in base form, sold for $36,785. Out of the 26,412 Corvettes sold in 1989, 9,749 were convertibles. 1,573 buyers optioned for $1,995 hardtop option.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1990/corvette/parts.html' => array(
                'title' => '1990 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1990 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1990 Corvette Parts" alt="1990 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1990.jpg" /></p>
<p dir="ltr"><span>It had been twenty years since a Corvette shipped from the factory with 300 horsepower. A lot changed in twenty years. The Corvette went from a manual transmission as standard, to an automatic. Modern port injection was used instead of a carburetor. Air Conditioning, Airbags, and Anti-lock brakes also became standard equipment. The speedometer on the Corvette looked like something out of a science fiction movie, and the Corvette used space-age plastics in its construction.</span></p>
<p dir="ltr"><span>The evolution of the Corvette culminated in the ZR-1. For several years, rumors circulated about the&rdquo;King of the Hill&rdquo; Corvette, and when it was unveiled at the Geneva Auto Show in 1989, and according to </span><span>Road and Track</span><span>, the ZR-1 was a &ldquo;world-class&rdquo; car. It&rsquo;s LT5 engine produced 375 hp, and delivered 300 lb.-ft of torque at only 1500 RPM (</span><a href="http://www.roadandtrack.com/new-cars/first-drives/reviews/a5975/1990-chevrolet-corvette-zr-1-drive-flashback/"><span>Road and Track</span></a><span>).</span></p>
<p><span id="docs-internal-guid-7a974ce7-c437-e430-983e-a3e1641a90d0"><span>After acquiring Lotus in 1986, GM started working with its engineers to produce a Corvette which could keep pace with the likes of Ferrari and Lamborghini. The joint team developed the dual overhead cam LT5 engine with the same bore spacing (4.4&rdquo;) as the L98 small-block, and contracted with Mercury Marine in Stillwater, OK, to build the LT5 entirely out of Aluminum. The completed engines were shipped to Bowling Green, where GM installed the LT5 in 3,049 Corvettes. RPO ZR-1 was available as on the coupe as a $27,016 option. It included all the bells and whistles, and had eleven inch rear wheels.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1991/corvette/parts.html' => array(
                'title' => '1991 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1991 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1991 Corvette Parts" alt="1991 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1991.jpg" /></p>
<p dir="ltr"><span>The ZR-1 &ldquo;goes fast better&rdquo; (</span><a href="http://www.caranddriver.com/comparisons/1991-chevrolet-corvette-zr-1-page-3"><span>Car and Driver, 1991</span></a><span>). For the 1991 model year, GM adopted the motto &lsquo;if it ain&rsquo;t broke, don&rsquo;t fix it&rsquo;, and left the Corvette largely unchanged. Base models started wearing body panels similar to the ZR-1, and wraparound parking/fog lights became standard on all models. The 1991 model also incorporated a delayed auto-shut off the electrical systems powering the stereo and power windows. This feature continued to deliver power to these features after the engine was turned off until the door was opened or until 15 minutes elapsed.</span></p>
<p dir="ltr"><span>With the advent of the ZR-1 in 1990, orders for the Callaway twin-turbo (RPO B2K) declined. In the first few years, sales for this option exceeded 100. By 1991, only 71 people had their Corvettes sent to Callaway to have the engine modifications made. </span></p>
<p><span id="docs-internal-guid-7a974ce7-c445-3691-f6f6-69027beecd2e"><span>If you buy a Corvette from 1984-91, the chances are that you&rsquo;ll see a fair amount of red cars. Chevrolet offered two versions of red for these years (Bright Red and Dark Red Metallic), and over one-third of the 250,503 Corvettes produced during the 84-91 period were painted one of these two shades. </span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1992/corvette/parts.html' => array(
                'title' => '1992 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1992 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1992 Corvette Parts" alt="1992 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1992.jpg" /></p>
<p dir="ltr"><span>The 1992 Corvette LT1 was &ldquo;the first all-weather Corvette&rdquo; (</span><a href="http://www.roadandtrack.com/new-cars/reviews/a9820/1992-chevrolet-corvette-lt1/"><span>Road and Track</span></a><span>). Acceleration Slip Regulation (ASR), or traction control, was introduced as standard equipment. Developed by Bosch, the system was adapted for the Corvette by GM engineers, and although it was engaged at ignition, the ASR system could be disabled with the push of a button above the headlight controls. The system worked to limit wheel spin and managed power output when it sensed low traction. The driver could feel additional resistance in the accelerator pedal when the system reduced throttle. Coupled with the exclusive for Corvette Goodyear GS-C tire, the &lsquo;92 Corvette was perfectly driveable in dreary weather, while its exotic competition remained in their garages.</span></p>
<p dir="ltr"><span>Also new for &lsquo;92 was the LT1 engine, which produced 300 hp and 330 lb.-ft of torque. Chevrolet changed its cooling system on the LT1, and used reverse-flow cooling. This unique system cooled the heads before sending coolant to the block, which made it possible for the engine to sustain higher bore temperatures and reduced ring friction. This, coupled with the use of synthetic Mobil 1 oil, which didn&rsquo;t heat as quickly as traditional oil, helped the engine operate more efficiently, without a separate oil cooler.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c457-85ec-c6fa-d6b099965d44"><span>1992 marked another milestone for the Corvette. On July 2, 1992, GM produced its one millionth Corvette: a white convertible with red interior and a black soft-top, just like the first one from 1953. GM donated this milestone Corvette to the National Corvette Museum in Bowling Green, KY. It was one of eight cars swallowed by the massive sinkhole in early 2014. GM plans to restore the car to its original condition.</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1993/corvette/parts.html' => array(
                'title' => '1993 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1993 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1993 Corvette Parts" alt="1993 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1993.jpg" /></p>
<p dir="ltr"><span>On modern cars, it&rsquo;s not uncommon to approach the car and open the door without ever unlocking the doors. The key fob acts as a transmitter, and once it reaches the right proximity to the vehicle, the doors unlock. Then, once inside the car, the driver merely pushes a button to start the vehicle. Chevy first installed this passive keyless-entry system on its 1993 Corvette. Drivers merely approached the Corvette and actuated the latch. Turning the ignition key caused the Corvette&rsquo;s 300 horses to scream to life, or in the case of the ZR-1, 405 hp. </span></p>
<p dir="ltr"><span>These additional horses prompted </span><span>Car and Driver</span><span> to include the ZR-1 on its &ldquo;Ten Best Performers&rdquo; list as the car with the highest top speed, at 179 mph (</span><a href="http://media.caranddriver.com/files/1994-10best-cars10best-10worst-performers-1993.pdf"><span>Car and Driver</span><span>, January 1994</span></a><span>). Over half of the 1993 ZR-1 cars were also optioned with RPO Z25, the 40th Anniversary Package. 1993 marked the 40th year of Corvette production, and GM commemorated this with the 40th Anniversary Package, which included Ruby Red interior and exterior, special wheel trim, and commemorative emblems. This package was available with all Corvette models, and leather seats, which became standard after &lsquo;93, all featured the 40th Anniversary emblem on the headrest. Out of 21,590 cars sold in &lsquo;93, 6,749 buyers chose to add the $1,455 Z25 option to their car. </span></p>
<p dir="ltr"><span>Always known as America&rsquo;s sports car, throughout its production, the Corvette continued to be priced at or near the median income for a typical U.S. household, and on the Corvette&rsquo;s 40th birthday, this was no different. In 1993, the reported median income in the United States was $29,819 (</span><a href="http://www.davemanuel.com/median-household-income.php"><span>Dave Manuel</span></a><span>). The base price of the 1993 Corvette coupe was $34,595, only a little higher than the median income during a less than optimal U.S. economy.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1994/corvette/parts.html' => array(
                'title' => '1994 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1994 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1994 Corvette Parts" alt="1994  Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1994.jpg" /></p>
<p dir="ltr"><span>The 1994 model year saw several changes to the Corvette. Leather seats became the standard for Corvette, since the cloth option was no longer available. Occupant safety was improved with the addition of a passenger airbag. R-134a refrigerant became standard for the Corvette&rsquo;s air conditioning, and the refreshed instrument panel graphics changed from white to the color tangerine at night.</span></p>
<p dir="ltr"><span>Since the reintroduction of the Convertible in 1986, Corvette owners asked for a glass rear window on the rag top. The tendency to fog up plagued the vinyl window, and over time, the vinyl also grew cloudy. In 1994, GM fixed this by installing glass rear windows in all convertibles. To sweeten the offering, GM also included a defogger grid in the rear window. </span></p>
<p dir="ltr"><span>Admiral Blue</span><span> and </span><span>Copper Metallic</span><span> were two new colors added in &lsquo;94, but </span><span>Copper Metallic</span><span> saw limited production (only 116 cars) due to concerns raised over paint quality.</span></p>
<p><span id="docs-internal-guid-7a974ce7-c4ac-74f7-0970-ef523043d4a9"><span>On September 2, 1994, the National Corvette Museum Foundation opened the National Corvette Museum in Bowling Green, KY. Built to showcase Corvettes from every generation, the museum is located directly across the street from the GM plant. Also unique to the museum is its program which allows new Corvette owners to take delivery of their cars at the museum. Visit the </span><a href="http://www.corvettemuseum.org/"><span>National Corvette Museum</span></a><span> in Bowling Green; it&rsquo;s the experience of a lifetime!</span></span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1995/corvette/parts.html' => array(
                'title' => '1995 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1995 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1995 Corvette Parts" alt="1995 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1995.jpg" /></p>
<p dir="ltr"><span>The color</span><span> Copper Metallic</span><span> from the &lsquo;94 Corvette was short-lived, and in &lsquo;95 was discontinued. It was replaced with </span><span>Dark Purple Metallic</span><span>. In 1995, the Bowling Green plant painted 1,049 cars this unique shade. 527 Convertibles received the Pace Car treatment, to celebrate the Indy 500, where Corvette served as the official pace car. White and black were popular colors on the &lsquo;95 car, but red still held the throne for the most popular C4 color.</span></p>
<p dir="ltr"><span>In years prior to &lsquo;95, the CD player found in the Corvette tended to skip when the car encountered bumps in the road. This error was addressed, along with several other cockpit improvements, on the 1995 car. GM stiffened the radio mount, installed hidden velcro straps to tie down loose components, and helped mitigate water leakage by modifying the weatherstripping to catch water drips.</span></p>
<p dir="ltr"><span>In the fall of 1993, Mercury Marine built the last LT5 engine, and all the manufacturing equipment was dismantled and shipped to GM. All LT5 engines were sent to Bowling Green, where they were kept in special storage until installation in a ZR1 Corvette. Although the first few years of the ZR1 saw high production, in model years 1993-95, only 448 ZR1&rsquo;s were produced each year. After building 6,939 ZR1 Corvettes over a six year period, GM discontinued the ZR1 after 1995.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1996/corvette/parts.html' => array(
                'title' => '1996 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1996 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1996 Corvette Parts" alt="1996 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1996.jpg" /></p>
<p dir="ltr"><span>In 1980, GM offered RPO L82 as a standalone engine option for the Corvette. This 350ci engine produced 230hp, and was available with any Corvette. After 1980, GM offered one engine option for the Corvette, except on special cars like the ZR1. In 1996, for the first time in sixteen years, and for 1996 only, GM offered the LT4 engine as a standalone engine option for the Corvette. It included the six speed manual (RPO MN6), and with its higher redline (6300 rpm), produced 330 hp. </span></p>
<p dir="ltr"><span>To celebrate the last year of the C4 Corvette, GM offered two special packages for the 1996 model year. RPO Z16 was the Grand Sport Package. All Grand Sport cars were painted </span><span>Admiral Blue</span><span> with white striping, and red hash marks on the left fender. Grand Sport cars were outfitted with sport seats, 5-spoke aluminum wheels (painted black), and the LT4 engine. 1,000 Grand Sports were made. Of these, only 190 were convertibles.</span></p>
<p dir="ltr"><span>The other special package offered for the &lsquo;96 Corvette was RPO Z15, the Collector Edition. It was available with any Corvette, and included silver 5-spoke wheels, sport seats embroidered with &ldquo;Collector Edition&rdquo;, and wide tires.</span></p>
<p dir="ltr"><span>Total production for the 1996 model year was 21,536. 4,369 convertibles were built, and 17,167 coupes rounded out production.</span></p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/7668" category_ids="7668,7669,7670,7677,7683,7689,7690,7691,7704,7711,7712,7713,7721,7722,7726,7723,7727,9007,7735,7728,7740,7733,7729,7730,7724,7731,7732,7741,7742,7743,9008,7744,7745,7746,7747,7748,7749,7750,7751,7752,7753,8537,7754,7767,7768,7778,7779,7783,7784,7793,7798,7802,7803,7806,7807,7808,7814,7792,7815,7816,7817,7822,7823,7824,7825,7826,7827,7828,7829,7830,7831,7832,8654,7833,8652,7834,7835,7836,7837,7838,7839,7840,7841,7842,7843,7844,7845,7846,7847,7848,7849,7850,8626,8623,8624,8620,8622,8621,8627,7851,7852,7853,7854,7855,7856,7857,7858,7859,7860,7861,7879,7880,7881,7882,7883,7888,7889,7890,7891,7892,7893,7900,7901,7902,7903,7904,7918,7919,7920,7921,7922,7923,7924,7925,7926,7938,7939,7942,7943,7944,7945,7946,7947,7948,7949,7950,7951,7952,7953,7954,7955,7956,7957,7958,7959,7960,7961,7962,7963,7964,7965,7966,7967,7968,7972,7973,7975,7976,7977,7978,7984,7985,7986,7987,7988,7989,7990,7991,7992,7993,7994,7995,7996,8000,8001"}}</p>'
            ),
            '1997/corvette/parts.html' => array(
                'title' => '1997 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1997 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1997 Corvette Parts" alt="1997 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1997.jpg" /></p>
<p>The C5 Corvette was designed, from the ground up, as the C5. Never in the history of the Corvette, did a car not have carry over parts from a previous Corvette, until the C5. Wait, what about the first generation car? Well, that car borrowed most of its components from other cars in the GM lineup. The C5 is all Corvette. According to the Corvette Black Book, &ldquo;virtually all interior, exterior, and suspension components were redesigned for this vehicle.&rdquo;</p>
<p>Notable about the C5 are its transaxle and the LS1 engine. The transaxle combined the transmission with the rear axle. By moving the transmission to the rear of the vehicle, GM was able to achieve perfect weight balance between the front and rear of the vehicle. The new six-speed transmission was inspired by the one found on the z/28 Camaro, but included an updated CAGS system in order to beat the EPA gas guzzler tax.</p>
<p>The LS1, built in Romulus, MI, was crafted entirely out of aluminum alloys, and was 44 lbs lighter than 1996&rsquo;s LT4. Instead of the DOHC design of the previous engines, the LS1 utilized a pushrod architecture, and produced a better-than-LT4 345 hp. 9,752 coupes were built, and each one retailed for $37,495.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '1998/corvette/parts.html' => array(
                'title' => '1998 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1998 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1998 Corvette Parts" alt="1998 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1998.jpg" /></p>
<p>Production for the &lsquo;97 Corvette was low, mainly due to a shorter production run, but in 1998, GM tripled production from the previous model year, and introduced the C5 Convertible. Chevy sold a total of 31,084 cars in &lsquo;98, and 11,849 of these were the new convertible.</p>
<p>The convertible marked the first time since 1962 that the Corvette was equipped with a traditional trunk. Its 13.9 cubic feet of storage space are easily accessible through the lid at the rear deck, and since the convertible top is not powered, plenty of trunk room remains even with the top down.</p>
<p>The 1998 Indianapolis 500 was paced by Corvette, and in celebration, GM continued its tradition of releasing pace car commemorative editions. The &lsquo;98 pace car was a convertible painted purple. Its interior was black and yellow, and its wheels were painted yellow. RPO Z4Z was available with the manual transmission for $5,039, and 1,163 cars were sold.</p>
<p>The Active Handling System (RPO JL4) helped manage handling and traction during slippery road conditions, and through a computer system, applied specific amounts of brake force to individual wheels to eliminate wheel slippage, oversteer, and understeer, regardless of how much throttle was applied.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '1999/corvette/parts.html' => array(
                'title' => '1999 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '1999 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1999 Corvette Parts" alt="1999 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/1999.jpg" /></p>
<p>The real star of the 1999 Corvette show was the new hardtop model. It&rsquo;s MSRP was $394 cheaper than the coupe, and $6,802 cheaper than the Convertible from which it took its styling cues. The hardtop was eighty pounds lighter than the other two &lsquo;vettes, and it included the manual transmission and Z51 suspension from the factory. It was essentially the convertible with a fixed hardtop, and since the top was always attached, it was 12% stiffer than the coupe (Corvette Black Book).</p>
<p>To help keep costs down on the hardtop, Chevy limited what options were available. Only five colors (white, Light Pewter, Nassau Blue, Black, and Torch Red) were available, and standard black interior was the only choice. The new for &lsquo;98 telescoping steering wheel wasn&rsquo;t available on the hardtop either, but RPO UV6, the heads-up display, was offered on all models as an $375 option.</p>
<p>In its first year, the hardtop only sold 4,031 cars. 18,078 coupes sold, and 11,161 convertibles moved off of dealer lots, bringing total production for the 1999 model year to 33,270.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '2000/corvette/parts.html' => array(
                'title' => '2000 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2000 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2000 Corvette Parts" alt="2000 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2000.jpg" /></p>
<p dir="ltr"><span>Y2K didn&rsquo;t harm the Corvette one bit. Corvette sales rose slightly, to 33,682 cars. The &lsquo;00 Vette entered the new millennium much the same as it was in 1999, but Bowling Green added three new colors to the lineup. </span><span>Torch Red</span><span> replaced </span><span>Firethorn Red</span><span>, and was the most popular color with 6,700 orders placed. The other new colors, </span><span>Millenium Yellow</span><span> and </span><span>Dark Bowling Green Metallic</span><span>, sold 3,578 and 1,663, respectively. </span></p>
<p>Passive Keyless Entry, which unlocked the doors when the key fob was in range, was discontinued due to owner complaint, and Active Keyless Entry was installed instead. The cylinder lock was removed from the passenger side, but was retained on the driver&rsquo;s side of the vehicle in case of battery failure.</p>
<p><span>The wheels on the 2000 Corvette were changed, and the Polished Aluminum Wheels (RPO QF5) were installed on 15,204 cars. Selective Real Time Damping and the Z51 performance package both received slight modifications which improved vehicle handling and occupant comfort.</span></p>
<p>The coupe remained the most popular model, with 18,113 sold for a standard MSRP of $39,475. The Convertible sold for $45,900, and 13,479 cars moved off of dealer lots. The hardtop sold only 2,090 cars, but retained its low MSRP of $38,900.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '2001/corvette/parts.html' => array(
                'title' => '2001 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2001 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2001 Corvette Parts" alt="2001 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2001.jpg" /></p>
<p>The first 199 Z06 Corvettes were made for the 1963 model year, and are some of the most collectible Corvettes ever made. In a salute to the past, Chevy unveiled a new Z06 Corvette for the 2001 model year. The 2001 Z06 was the first mass-produced car to incorporate a titanium exhaust system (<span>Corvette Black Book</span><span>), and unleashed its 385 hp LS6 through a revised 6-speed manual transmission.</span></p>
<p>The Z06 started life as the hardtop from previous years, and like that hardtop, was limited to only five color options (most were black). To further set the Z06 apart, it received a unique wheel design, black, or black/red leather interior, and Goodyear Eagle F1 Supercar tires. To accommodate the more powerful LS6, GM increased the maximum readout on the analog tachometer, and applied special backgrounds to the Z06 instrument panel.</p>
<p>Chevy sold 5,773 Z06 Corvettes in 2001. The MSRP on the Z06 was $47,500, or $500 more than the standard Corvette convertible. The coupe, which became the cheapest Corvette model, sold 15,681 models at the MSRP of $40,475. Total production for the 2001 model year was 35,627 Corvettes. Torch Red was the most popular color, but it was closely trailed by black.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '2002/corvette/parts.html' => array(
                'title' => '2002 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2002 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2002 Corvette Parts" alt="2002 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2002.jpg" /></p>
<p>The Z06 continued to be the star of the 2002 Corvette show, but the regular coupe and convertible also saw a few moderate changes for the &lsquo;02 model year. All &lsquo;02 Z06 cars and 10,964 RPO 1SC convertibles used thinner 4.8mm glass on the windshields, which saved 2 ⅔ pounds on each car. Previously optional on the Corvette, floor mats became standard equipment in 2002.</p>
<p>The LS6 engine was overhauled in 2002. It&rsquo;s output increased to 405 hp and 400 lb.-ft of torque. Two small catalytic converters were removed and exhaust back pressure was reduced by 16%. The redesign of the two primary converters under the the floor allowed the Corvette to meet Nationwide Low Emission Vehicle standards. The camshaft was modified such that it achieved the highest lift in small-block history.</p>
<p>All 2002 Z06 cars received front fender badges which read: &ldquo;Z06 405 HP&rdquo;. Only five colors were available on the Z06, but the coupe and convertible cars could be painted in eight different colors. With the exception of the 1996 model year, red was the top selling color for all C4 Corvettes, and for all C5&rsquo;s until 2002. In 2002, black outsold red by 267 cars. Total production for the year reached 35,767 cars. The Z06 sold 8,297 hardtops, but was still outsold by the coupe (14,760) and convertible (12,710).</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '2003/corvette/parts.html' => array(
                'title' => '2003 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2003 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2003 Corvette Parts" alt="2003 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2003.jpg" /></p>
<p>In June of 1953, the first two Corvettes rolled off the assembly line in Flint, MI. Fifty years later, in 2003, GM sold 11,632 Corvettes with the 50th Anniversary Edition package. The Corvette was painted Polo White, with red interior. According to Car and Driver, using this same color scheme on the 50th anniversary car would be &ldquo;just too obvious&rdquo; (<a href="http://www.caranddriver.com/features/2003-chevrolet-corvette-50th-anniversary-special-edition-feature"> July, 2002</a>). Instead, Chevrolet created a special shade of burgundy called <span>Anniversary Red. This rich color has flecks of aluminum oxide mixed in with the paint to give it a special sheen, and the clear coat was formulated to highlight the paint color. The interior (and convertible soft top), are shale colored, with dark gray on the console, instrument panel, and doors to give contrast.</span></p>
<p>The standard equipment list on coupes and convertibles grew as GM added fog lamps, dual zone climate control, sport seats, and the power passenger seat to all 2003 models. Magnetic Selective Ride Control (RPO F55) also made its first appearance on 2003 Corvettes. This revolutionary new technology replaced the Real Time Damping System found on earlier Corvettes, and instead of using traditional fluid in the shocks, it used magnetic fluid to adjust how the pistons and valves functioned. At highway speeds, this new system, coupled with a powerful microprocessor, can adjust shock damping 1000x per second.</p>
<p>RPO F55 was installed in 14,992 coupes and convertibles. The Z06 continued to employ FE4 suspension. Total production for the &lsquo;03 model year reached 35,469, more than 118 times total 1953 production.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '2004/corvette/parts.html' => array(
                'title' => '2004 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2004 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2004 Corvette Parts" alt="2004 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2004.jpg" /></p>
<p>The C5 Corvette was produced from 1997-2004. During that time, the Corvette celebrated its fiftieth birthday, saw great success at the Le Mans racing series, and achieved supercar status with the &lsquo;04 Z06, which lapped the Nurburgring in under 8 minutes. The LS1 engine produced more horsepower than any base Corvette engine since the sixties, and the head-up display was technology right out of a fighter plane.</p>
<p>To celebrate the success of the C5 both in the market and on the racetrack, GM released a Commemorative Edition package. Available with any &lsquo;04 Corvette, the car received special badging, and Le Mans Blue paint. The Z06 commemorative car received several unique features of its own. Instead of a fiberglass hood, the Z06 became the first regular production car to be equipped with a carbon fiber hood. The Z06 also borrowed the stripe scheme used on the C5R to further delineate that the Z06 was really a car built to go fast.</p>
<p>Production for the last year of the C5 was high, in spite of the anticipated C6 for 2005. Total production for the Commemorative Edition was 6,899, but the total was split evenly amongst the three available body styles. Total production for the year was 34,064, and the coupe was still the top seller.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8003" category_ids="8003,8020,8083,8096,8047,8038,8069,8081,8057,8098,8102,8123,8143,8144,8151,8152,8015,8164"}}</p>'
            ),
            '2005/corvette/parts.html' => array(
                'title' => '2005 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2005 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2005 Corvette Parts" alt="2005 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2005.jpg" /></p>
<p>Former U.S. Secretary of State Colin Powell paced the 2005 Indianapolis 500 in the all new C6 Corvette. This new Corvette sported a smaller body and external headlights. In fact, even though the Corvette&rsquo;s layout (FR) remained unchanged from the C5, the body and interior were all-new for &lsquo;05. The chassis was modified, and overall dimensions changed. Overall length and width both shrunk by 5.1 and 1.1 inches, respectively. GM also reduced body overhang by lengthening the wheelbase 1.2 inches.</p>
<p>The exposed headlights were new for the C6, and a departure from the previous four Corvette generations, which employed a hidden headlight design. The headlight housing enclosed Xenon low-beams, tungsten-halogen high-beams, parking and running lights, and the turn indicators.</p>
<p>Passive Keyless Entry returned with a new trick for the C6. The new system unlocked the Corvette when the keyfob was in proximity, but it also activated the push-button ignition system. Other new convenience features available as standalone options included DVD based navigation, OnStar, XM radio, heated seats, and a powered convertible top.</p>
<p>The new-for-the-C6 LS2 displaced 6.0L and sent 400hp screaming to the rear wheels through its 6-speed manual or optional 4-speed automatic. Total production for the year was 37,372. 10,644 convertibles were sold, despite late-to-start production.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2006/corvette/parts.html' => array(
                'title' => '2006 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2006 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2006 Corvette Parts" alt="2006 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2006.jpg" /></p>
<p>The 2006 C6 was the Corvette fans were waiting for. In 2006, Chevy reintroduced the Z06 and it&rsquo;s 7.0L LS7 engine. The lightest Corvette built to date, the C6 Z06 used an aluminum frame and was 30% lighter than the steel-framed coupe. The small-block LS7 generated 470 lb.-ft of torque and 505hp, a Corvette first. It used a dry-sump oiling system, achieved a compression ratio of 11:1, and the redline was set at 7000rpm.</p>
<p>The Z06 may have been the star of the show in &lsquo;06, but its $65,800 MSRP was nearly twenty thousand dollars more than the base coupe. The standard convertible and coupe were plenty fast, as their LS2 engine generated 400 hp, and the new six-speed automatic was available as an option exclusively on the coupe and convertible. This transmission offered computer control and multiple driving modes, and shift-paddles were affixed to the new three-spoke steering wheel.</p>
<p>All 2006 &lsquo;Vettes benefitted from the new chrome accents on interior trim, and various trim packages could be optioned. For example, package 3LT on the Convertible included the head-up display, the power-telescoping steering column, auto-dimming mirrors, heated seats, a built-in garage door opener, and an upgraded sound system with XM radio.</p>
<p>Total production for &lsquo;06 reached 34,021, but the convertible gained ground over the previous year, and sold 11,151 cars.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2007/corvette/parts.html' => array(
                'title' => '2007 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2007 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2007 Corvette Parts" alt="2007 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2007.jpg" /></p>
<p>The 2007 Corvette outsold every Corvette since 1984, when Chevy set its all-time sales record of 51,547 coupes, by selling 40,561 cars for the 2007 model year. Two special edition cars were introduced for &lsquo;07. 500 Z4Z Indy 500 Pace Car replicas and 399 Z33 Ron Fellows Z06 Special Edition cars were available for $66,995 and $77,500 respectively.</p>
<p>RPO F55 Magnetic Selective Ride Control continued to be available, and was sweetened for the 2007 model year with larger, cross-drilled brake rotors. These rotors improved the braking of the base Corvette while still maintaining the road-friendly suspension. In previous models, the only way to upgrade Corvette brakes was by optioning for RPO Z51, which had a much harsher ride than standard suspension packages.</p>
<p>In the leadup to the U.S. recession, the 2007 Corvette was the best-selling C6, and was praised by journalists as being more comfortable and as high performance as many European competitors. The long list of options allowed each Corvette buyer to buy a made-to-measure car which suited his particular needs. The 6-speed manual soldiered on as the standard transmission, but over half of the Corvettes sold in &lsquo;07 were equipped with the automatic gearbox.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2008/corvette/parts.html' => array(
                'title' => '2008 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2008 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2008 Corvette Parts" alt="2008 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2008.jpg" /></p>
<p>&ldquo;The [2008] Vette is one of those sports cars that you wouldn\'t hesitate to drive daily&rdquo; (<a href="http://www.caranddriver.com/reviews/2008-chevrolet-corvette-road-test">Car and Driver, September 2007</a>). The new LS3 engine was standard on coupe and convertible, and produced 430 hp. An additional 6 horsepower were generated when the LS3 was paired with RPO NPP Dual Mode Exhaust System. This exhaust system helped the Corvette breathe freer by using a vacuum system to operate additional outlet valves.</p>
<p>The modified shift linkage on the manual transmission offered more positive gear selection and shorter throws. In conjunction with the revised tuning on the steering system, the Corvette became a more tractable car in the corners, and more pleasant as an everyday car.</p>
<p>The Corvette options list grew longer in 2008, but features like XM radio, OnStar, and an auxiliary input became standard features on all cars. Black was far and away the most popular color in 2008, and was painted on more Z06 cars than any of the other available colors.</p>
<p>This, the 55th year of Corvette production, proved to be the last year of high-volume production. It&rsquo;s total of 35,310 cars built would be halved in 2009 due to a worldwide economic downturn, and would continue to stay below twenty thousand for the next few years. In spite of this, the C6 received rave reviews from journalists and consumers alike, and easily bested cars twice its price in both performance and features.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2009/corvette/parts.html' => array(
                'title' => '2009 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2009 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="1953 Corvette Parts" alt="1953 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2009.jpg" /></p>
<p>&ldquo;ZR1. ZR1. ZR1. The fastest and most powerful car GM has ever built becomes available for public consumption in 2009. We\'ve driven it, and it was nothing short of "shock and awe" inspiring&rdquo; (<a href="http://www.motortrend.com/roadtests/coupes/112_0809_2009_chevrolet_corvette_first_look/">Motor Trend, September 2008</a>).</p>
<p>This ZR1 Corvette was, at its time, the most powerful car ever produced by GM. Its top speed was rated over 200mph, and with 638hp in a 3350lb. car, it easily achieved supercar speeds. It borrowed the aluminum chassis from the Z06, but it employed Selective Magnetic Ride Control instead of the standard suspension used in the Z06. The carbon fiber hood had a special window which displayed the intercooler and the supercharged 6.2L LS9 engine underneath. The ZR1 retailed for $103,300, but still included steering wheel mounted audio controls and other creature comforts just like every other Corvette. Unlike many supercars, the ZR1 was perfectly driveable under normal road conditions, and was enjoyed by 1,415 buyers in 2009.</p>
<p>Other Corvette models remained largely unchanged for the 2009 model year, and production was more than half of the previous years. In spite of this, the Corvette was still a successful car, and the MSRP remained consistent with previous models. It was available in ten colors, black being the most popular, and boasted all the creature comforts not found on cars costing twice as much.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2010/corvette/parts.html' => array(
                'title' => '2010 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2010 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2010 Corvette Parts" alt="2010 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2010.jpg" /></p>
<p>In spite of corporate struggles at GM, 2009 and 2010 were fantastic years for Corvette development and innovation. In &lsquo;09, Chevy unveiled the ZR1, which was the most powerful vehicle ever produced by General Motors. For the 2010 model year, GM released the Corvette Grand Sport. Enthusiasts might remember the Grand Sport as a special edition car from 1963 and &lsquo;96, but the 2010 Grand Sport returned as a regular production car.</p>
<p>For $5,840 more than the standard coupe, a Grand Sport buyer receives a lot of vroom for his buck. For starters, the Grand Sport borrows the front splitter and rear spoiler from the Z06. Next, GM designers built wider front and rear fenders, to better match the wide styling of the Z06. On cars with manual transmissions, the dry-sump oiling system from the LS7 was added, as was launch control (included on all manual Corvettes). Z06 brakes and tires were combined with updated Z51 suspension (Grand Sport replaced RPO Z51), and the Grand Sport nicely occupied the performance gap between the $75,000 Z06 and the base Corvette.</p>
<p>Total Grand Sport production (6,042) made up about half of the Corvettes sold for the 2010 model year. GM built a total of 12,194 Corvettes, but the options list stayed long, and every Corvette could be customized to the owners delight. All eight colors were available on all models, and the ever-popular Torch Red reappeared as the second most popular color for 2010.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2011/corvette/parts.html' => array(
                'title' => '2011 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2011 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2011 Corvette Parts" alt="2011 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2011.jpg" /></p>
<p>The 2011 Corvette returned with a fury of squealing new rubber. The Z06 and some Grand Sport&rsquo;s were outfitted with all new Goodyear Eagle F1 Supercar Gen 2 tires. In Car and Driver tests, the 2011 Grand Sport Convertible, equipped with Magnetic Ride Control and the Goodyear tires, achieved 1.06G on the skidpad (<a href="http://www.caranddriver.com/reviews/chevrolet-corvette-review-2011-chevy-corvette-gs-convertible-test">Car and Driver, September 2010</a>). The Z06 and ZR1 cars both achieved 1.07G at the skidpad.</p>
<p>The 2011 Corvettes were largely unchanged from 2010, but the Z06 boasted several new exclusive options, and a special edition. RPO CFZ was the Carbon Fiber package, which replaced several traditional components on the Z06 with carbon fiber. The front splitter, rockers, and roof panel were all black carbon fiber, while the rear splitter was borrowed from the ZR1 and painted the body color. The Z07 Performance package included Magnetic Ride Control, Brembo brakes, and ZR1 tires and wheels.</p>
<p>In 1960, three Corvettes were entered in the Le Mans racing series, and ever since, the Corvette has been a strong presence on the world racing circuit. To celebrate 50 years of Le Mans racing, Chevy unveiled the 2011 Z06 Carbon Limited Edition. It included all the features of RPO Z07, a carbon fiber hood, and was limited to just two colors, Inferno Orange and Supersonic Blue. Total production for the Z06 Carbon Edition was just 252 cars.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2012/corvette/parts.html' => array(
                'title' => '2012 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2012 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2012 Corvette Parts" alt="2012 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2012.jpg" /></p>
<p>1912 was the first full year the Chevrolet was in business, so to celebrate its centennial, Chevrolet offered a Centennial edition Corvette package. It was available with all models, and offered special Carbon Flash paint, black wheels with red accents and calipers, red accented interior, and Magnetic Ride Control as the standard package features.</p>
<p>One of the biggest complaints about the C6 was the lack of support in the seats, so for the 2012 model year, Chevy reinforced the seat bolsters in an effort to improve lateral support, which were lauded by Car and Driver as having more grip on the occupants (<a href="http://www.caranddriver.com/news/2012-chevrolet-corvette-z06-and-zr1-news">Car and Driver, April 2011</a>).</p>
<p>Optional caliper colors became available in 2012. Silver, gray, yellow, and red were all available as $595 options, and were optioned on 8,769 Corvettes. The Grand Sport Coupe was the most popular seller in 2012, and accounted for 43% of 2012 Corvette production. The base coupe and Grand Sport Convertible sold 24% and 19%, respectively.</p>
<p>The Z06 and ZR1 cars soldiered on as low-production cars. Both sold less than 500 cars, but continued to outpace many competitors with much higher pricetags. The 2012 Corvette sold 11,647 cars, and, as the end of the generation neared, continued to be America&rsquo;s favorite sports car.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2013/corvette/parts.html' => array(
                'title' => '2013 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2013 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2013 Corvette Parts" alt="2013 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2013.jpg" /></p>
<p>&ldquo;Chevrolet finally builds a Z06 convertible. Almost&rdquo; (<a href="http://www.caranddriver.com/reviews/2013-chevrolet-corvette-427-convertible-instrumented-test-review">Car and Driver, September 2012</a>). This car is the 2013 427 Corvette Convertible. Built on the steel frame of the standard Corvette, the 427 shares most of its DNA with the Z06. The 427 is powered by the 505 hp LS7, outputs power through the Z06&rsquo;s 6-speed manual transaxle, and rode on the Magnetic Ride Control suspension. Built to celebrate the final year of C6 production, it was available in all the standard colors and could option for the 60th Anniversary package. Even though the 427 Convertible wasn&rsquo;t totally a drop-top Z06, it was close, and easily reached speeds in excess of 170 mph.</p>
<p>The 60th Anniversary package offered Arctic White paint, blue accents and convertible top, and the ZR1 spoiler. It was available on all Corvette models, if the right equipment group was selected, and was installed on 2,059 2013 Corvettes.</p>
<p>For the final year of C6 production, GM decided to celebrate. It&rsquo;s 2013 ZR1 paced the Indy 500 with Guy Fieri inside. The final C6 Corvette built, a 60th Anniversary Edition 427 Convertible, rolled off the plant in Bowling Green with an engine built by the Corvette&rsquo;s chief engineer, Tadge Juechter (<a href="http://www.motorauthority.com/news/1082636_last-c6-chevrolet-corvette-rolls-off-the-line">Motor Authority, March 2013</a>). Finally, total production for 2013 reached 13,466. Although not the highest production year for the C6, 2013 was a remarkably strong build-up for the new for 2014 C7.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8179" category_ids="8179,8194,8244,8208,8220,8213,8232,8241,8700,8260,8287,8546,8295,8304"}}</p>'
            ),
            '2014/corvette/parts.html' => array(
                'title' => '2014 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2014 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2014 Corvette Parts" alt="2014 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2014.jpg" /></p>
<p>The C7 Corvette was first unveiled to the public on January 14, 2013. Production began for the 2014 model year in the summer of 2013, and the car was in showrooms by September. This much anticipated Corvette incorporated so much modern technology, that it used two 8-inch LCD computer screens to communicate vehicle information to the driver. The C7 introduced many new standard features to the base Corvette, including an all-aluminum frame, which weighed 100 pounds less while providing 50% more rigidity. Also standard on the C7 was a removable carbon fiber roof panel.</p>
<p>The C7 was designed in a wind tunnel with the goal of much better aerodynamics than the previous generation C6. Electronic power steering and a 7-speed manual transmission helped improve fuel economy, but the cylinder deactivation technology found in the all-new LT1, helped the C7 achieve an EPA estimated 29 mpg on the highway.</p>
<p>The new LT1 displaced 6.2L and generated 455 hp from a traditional pushrod engine. The engine incorporated technologies like continuously variable valve timing and direct fuel injection. This precise engine was built in the USA at the factory in Tonawanda, New York, before being shipped to the Bowling Green plant for installation.</p>
<p>The C7 Stingray was available in coupe and convertible versions, with the Z51 performance trim optional on both. The base coupe retailed for $51,995, and its topless counterpart sold for $5,000 more. Total production for the 2014 model year was 37,288, nearly three times 2013 production.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8535" category_ids="8535,8528,8554,8532,8540,8541,8529,8530,8527,8555,8556,8581"}}</p>'
            ),
            '2015/corvette/parts.html' => array(
                'title' => '2015 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2015 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2015 Corvette Parts" alt="2015 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2015.jpg" /></p>
<p>New for the 2015 Corvette was the inclusion of MyLink/OnStar and 4G LTE. Also new was the GM-built 8-speed automatic transmission, which rivaled dual-clutch gearboxes from companies like Porsche.</p>
<p>The Performance Data Recorder added video and audio recording equipment in order to monitor and save the driver&rsquo;s experience and save it to an SD card for viewing outside the car. In addition to track usage, the system also included a valet mode to monitor the vehicle when not being driven by its owner.</p>
<p>The real showstopper for the 2015 model year was the Z06. Available in both coupe and convertible configurations, the Z06 was powered by a supercharged LT4 engine and could be mated to either the 8-speed automatic or 7-speed manual transaxle. It generated 650hp and 650 lb-ft. of torque. 0-60 was 3.3 seconds, and the car achieved more than 1.1g on the skidpad. Many compared the car to a cross between a C6 Z06 and ZR1. Regardless, the 2015 Z06 was GM&rsquo;s newest supercar.</p>
<p>Total production for the 2015 model year was 34,240 cars. Of this, 8,653 were Z06 cars. Only 11,008 cars were equipped with the seven-speed manual. The remaining cars were equipped with the all-new and super-quick 8-speed automatic. Arctic White and Torch Red were the top colors, and only 47 cars shipped with Suede Dark Grey interior (all 47 were Z06 cars).</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8535" category_ids="8535,8528,8554,8532,8540,8541,8529,8530,8527,8555,8556,8581"}}</p>'
            ),
            '2016/corvette/parts.html' => array(
                'title' => '2016 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2016 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2015 Corvette Parts" alt="2015 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2015.jpg" /></p>
<p>The Z06 returned in 2016, offering more choices for personalization to complement its world-class levels of performance &ndash; including an all-new C7.R Edition. The C7.R Edition paid tribute to the Corvette Racing C7.R racecars. It was offered in Corvette Racing&rsquo;s signature yellow livery &ndash; or black &ndash; with coordinated exterior and interior accents. Only 500 were built, all with the Z07 Performance Package with carbon ceramic brakes, and a specially serialized vehicle identification number.</p>
<p>Additionally, three all-new color-themed design packages &ndash; Twilight Blue, Spice Red and Jet Black Suede &ndash; offered custom-tailored appearances that elevated the Z06&rsquo;s presence on the street or track, while features such as an available front parking camera and power-cinching latch for the coupe&rsquo;s hatchback or convertible&rsquo;s trunk enhanced convenience &ndash; and added an extra measure protection.</p>
<p>New smartphone projection technology on Corvette Z06&rsquo;s MyLink system displayed content from Apple iPhone 5 or later models on the multicolor screen through the Apple CarPlay feature. Supported apps for the system included phone, messages, maps, music and compatible third-party apps.</p>
<p>A total of 40,689 2016 Corvettes were produced. The highest selling model was the Stingray Coupe, garnering 52.6%, followed by the Z06 Coupe with 28.4%. Stingray Convertibles accounted for 12.4% and Z06 Convertibles trailed with 6.7%.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8535" category_ids="8535,8528,8554,8532,8540,8541,8529,8530,8527,8555,8556,8581"}}</p>'
            ),
            '2017/corvette/parts.html' => array(
                'title' => '2017 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2017 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2015 Corvette Parts" alt="2015 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2015.jpg" /></p>
<p>The seventh generation Corvette came into full realization with the return of the Grand Sport in 2017, joining Stingray and Z06. The new Grand Sport built on a legacy established in 1963, when five Grand Sport race cars were built under the direction of the Corvette&rsquo;s first chief engineer, Zora Arkus-Duntov.</p>
<p>With Corvette Racing in its DNA, the all-new 2017 Corvette Grand Sport was a pure expression of the car&rsquo;s motorsports-bred pedigree. Combining a lightweight architecture, track-honed aerodynamics package, Michelin tires and a naturally aspirated engine, the Grand Sport offered an estimated 1.05g in cornering capability &ndash; and up to 1.2g with the available Z07 package. Offers estimated 0-60-mph performance of 3.6 seconds and quarter-mile capability of 11.8 seconds at 118 mph, with the available Z07 performance package and available paddle-shift eight-speed automatic transmission. With it\'s aggressive, wide body, the Grand Sport looked right at home on the grid at Sebring. Fender vents had a signature side cove that included the Grand Sport badge. A front splitter, special rockers and a rear spoiler with wickers were all standard on the 2017 Grand Sport.</p>
<p>Both the 2017 Stingray and 2017 Grand Sport boasted a 6.2L LT1 V8 engine with 460-horsepower and 465 lb.-ft. of torque. The 2017 Z06 boasted an LT4 Supercharged 6.2L V8 engine with 650-horsepower and 650 lb.-ft. of torque. The Z06 clocked 0-60mph in 2.95 seconds.</p>
<p>For the 2017 model year, Chevrolet built a total of 32,782 Corvettes. The number one selling model was the Corvette Stingray Coupe which accounted for 34.3% of production, followed by the Grand Sport Coupe which saw 30.2% of production while the Z06 had 18.9% of 2017 Corvette production.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8535" category_ids="8535,8528,8554,8532,8540,8541,8529,8530,8527,8555,8556,8581"}}</p>'
            ),
            '2018/corvette/parts.html' => array(
                'title' => '2018 Chevrolet Corvette Parts and Accessories',
                'layout' => '1column',
                'heading' => '2018 Corvette Parts',
                'content' =>'<p><img style="margin: 0px 10px 10px 0px; border: 2px solid #cccccc; float: left;" title="2015 Corvette Parts" alt="2015 Corvette Parts" src="https://s3.amazonaws.com/zip-corvette/web/cms-pages/year-landing-pages/2015.jpg" /></p>
<p>2018 was a shortened model year for the Chevrolet Corvette - with only 9,670 Corvettes built.</p>
<p>New for the 2018 Corvette was the special Carbon 65 Edition package - a nod to Corvette\'s 65th anniversary. The $15,000 package was available on 2018 Grand Sport 3LT and Z06 3LZ models and featured a Ceramic Matrix Gray exterior with unique fender stripes and door graphics. Black wheels, blue brake calipers and a carbon fiber spoiler gave this Corvette an attitude all its own. The Carbon 65\'s were truly limited edition - only 650 were produced globally.</p>
<p>Also new in 2018 were slightly bigger standard rear wheels on the Corvette Stingray, along with five new wheel options. The 2018 Z06 also offered new wheel choices.</p>
<p>All 2018 Corvette models came equipped with HD digital radio and improved rear-view camera imaging. Each Corvette also included an enhanced Head-Up Display rotation setting. Ceramic Matrix Gray replaced Sterling Blue in the exterior color palate for 2018.</p>
<p>The top selling color for 2018 was Artic White, followed by Black and Torch Red.</p>
<p>Once again, the Stingray Coupe was the highest selling model with 31.7% of total sales. The Grand Sport Coupe came in second with 26.5% of total sales with the Z06 Coupe close behind with 24.3%.</p>
<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8535" category_ids="8535,8528,8554,8532,8540,8541,8529,8530,8527,8555,8556,8581"}}</p>'
            ),

            'active-sales.html' => array(
                                'title' => 'Get Our Active Sales',
                                'layout' => '2columns-right',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Sign up to our Newsletter</span></p>
                <!--<p><img title="Email Sign Up" src="{{media url="wysiwyg/email-sign-up-landing.jpg"}}" alt="Email Sign Up" /></p>-->
                <p>Being a Zip Corvette Newsletter subscriber definitely has its perks. One major bonus being that you will have access to exclusive subscriber only Sales and Discounts.</p>
                <p><strong>Join our mailing list today to gain access to all of our active and future promotions!</strong></p>
                <form action="http://email.zip-corvette.com/public/webform/process/" method="post"><input type="hidden" value="41vrbeqb6z4zxjemu5clxev2uwgz6" name="fid" /> <input type="hidden" value="2cdb8ef4bb49bd5a3a5c35c299d7a019" name="sid" /> <input type="hidden" value="" name="delid" /> <input type="hidden" value="" name="subid" /> <input type="hidden" value="" name="td" /> <input type="hidden" value="addcontact" name="formtype" />
                <script type="text/javascript">// <![CDATA[
                var fieldMaps = {};
                // ]]></script>
                <div id="row_17291">
                <div id="column_21777" style="text-align: left; width: 600px;">
                <div>
                <div>Email Address</div>
                <div><span> <input type="text" size="35" value="" name="45638" /> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div>
                <div>Email Address (Verify) <span>*</span></div>
                <div><span> <input type="text" size="35" value="" name="45639" /> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                </div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                <div id="row_17292">
                <div id="column_21778" style="text-align: left; width: 600px;">
                <div>Please select your Corvette generation(s) below:</div>
                </div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_64625" class="checkbox" type="checkbox" value="1" name="45641[64625]" /> <label id="caption_64625" for="field_64625"> 53-62 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_64631" class="checkbox" type="checkbox" value="1" name="45642[64631]" /> <label id="caption_64631" for="field_64631"> 63-67 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_64632" class="checkbox" type="checkbox" value="1" name="45643[64632]" /> <label id="caption_64632" class="caption" for="field_64632"> 68-82 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_64633" class="checkbox" type="checkbox" value="1" name="45644[64633]" /> <label id="caption_64633" for="field_64633"> 84-96 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_64634" class="checkbox" type="checkbox" value="1" name="45645[64634]" /> <label id="caption_64634" for="field_64634"> 97-04 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_64635" class="checkbox" type="checkbox" value="1" name="45646[64635]" /> <label id="caption_64635" for="field_64635"> 05-13 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="display: inline-block; width: 10%;">
                <div><span> <input id="field_213448" class="checkbox" type="checkbox" value="1" name="45647[213448]" /> <label id="caption_213448" class="caption" for="field_213448"> C7 </label> </span>
                <div>&nbsp;</div>
                </div>
                </div>
                <div style="clear: both;">&nbsp;</div>
                <div id="row_17294">
                <div id="column_21781" style="text-align: left; width: 600px;">
                <div>
                <div><span> <input class="btn btn-default" type="submit" value="Submit" /> </span></div>
                </div>
                <input type="hidden" value="true" name="46623[200083]" /></div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                </form>
                <p style="display: none;"><span style="color: #ff6600;"><strong><em>active sale info and promotion codes will be displayed after submitting this form</em></strong></span></p>'
                            ),
                            'carlisle-2018.html' => array(
                                'title' => 'Carlisle 2018 Wrap Up',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>Just a big shout-out to everyone that came out to see us at #Corvettes at Carlisle 2018! Here is a wrap-up photo album of the show. Thanks to everyone for coming out to visit us! We enjoyed meeting and talking with all of you. We will see you all next year!</p>
                <p>Remember to <a title="Catalog Request" href="http://www.zip-chttps://www.zip-corvette.com/crimson_mach">request your FREE Catalog here today.</a></p>
                <div class="embedsocial-album" data-ref="1f2e0cfd0451770fab823416059c256543535f79">&nbsp;</div>'
                            ),
                            'championship-shipping-2018.html' => array(
                                'title' => 'Championship Flat Rate Shipping 2018',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><a title="Shop Corvette Parts and Accessories" href="http://www.zip-corvette.com/category.html"><img style="display: block; margin-left: auto; margin-right: auto;" title="Flat Rate Shipping" src="{{media url=&quot;landing-pages/vetts-nets-flat-rate-shipping-2018.jpg&quot;}}" alt="Flat Rate Shipping"></a></p>
                <p>Tonight, we crown a new basketball champion and Zip has one final offer to celebrate the madness!</p>
                <p>Enjoy $7.95 Flat Rate Shipping on all orders when you purchase online. Hurry - this offer expires at midnight Tuesday. No promo code needed, simply select flat rate shipping option at checkout.</p>
                <p><em>*Flat rate shipping applies to orders delivered to any of the 48 contiguous US only. All oversize charges, crate fees and truck freight charges still apply. Offer valid for retail customers only, must order online before 4/3/2018 at midnight ET. Not to be combined with any other discount or promotion.</em></p>
                <p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8716" category_ids="8716,8756,8771,8778,8793,8820,8825,8836,8847,8856,8868,8880,8895,8924,8935,8946,8962,8971,8995,8981"}}</p>'
                            ),
                            'christmas-delivery.html' => array(
                                'title' => 'Christmas Delivery',
                                'layout' => '2columns-right',
                                'heading' => '',
                                'content' =>'<p><img src="http://hosting-source.bm23.com/18315/public/newsletter_12152014/map_0103.gif" alt=""><br> <br><br></p>
                <div>
                <p style="line-height: 19px;"><span style="font-size: 11pt;"><strong>Below are the latest dates and times we can guarantee delivery of UPS Ground packages.</strong></span></p>
                <ul>
                <li><strong>Yellow States:</strong> Order on or before 12/22 at 3pm EST</li>
                <li><strong>Gold States:</strong> Order on or before 12/19 at 3pm EST</li>
                <li><strong>Green States:</strong> Order on or before 12/18 at 3pm EST</li>
                <li><strong>Brown States:</strong> Order on or before 12/17 at 3pm EST</li>
                <li><strong>Orange States:</strong> Order on or before 12/16 at 3pm EST</li>
                <li><strong>Grey States:</strong> Order on or before 12/15 at 3pm EST</li>
                </ul>
                </div>
                <p>Need Upgraded Shipping Services? While time is running out fast, you still have time to order last minute gifts before Christmas. Reference the map above to view our shipping schedule for Christams 2014. Need it even faster? Upgrade your shipping service by speaking to a representative via phone, email or live chat.</p>'
                            ),
                            'corvette-clubs' => array(
                                'title' => 'Corvette Clubs',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Corvette Club Information</span></p>
                <p>We are committed to offering support for Corvette Clubs. We realize that our most valued customers are the same people who plan car shows, cruise-ins and charity fund raisers for communities all over the country – sometimes even the world. Although we may be hundreds of miles away from your club’s events, we wants to be a part of those efforts.</p>
                <p>We encourage you to let us know about your club’s upcoming car shows and events. We are always willing to provide “goodies” for your show. We also appreciate the opportunity to reach out to your club members with special discounts and offers.</p>
                <p><a title="Contact Us" href="http://www.zip-corvette.com/contacts">Click Here</a> to submit a request for items for an upcoming car show. All we ask in return is that you consider linking the exact phrase "Corvette Parts" to our website (<a href="http://www.zip-corvette.com/" target="_blank" rel="noopener">http://www.zip-corvette.com</a>/) on your club’s web site and encourage your members to give us a call the next time they need something for their Corvette. We have more than 25,000 items for seven generations in stock and ready to ship!</p>
                <h2>Please provide the following when submitting a request:</h2>
                <p>Contact Name:</p>
                <p>Contact e-mail address and phone number:</p>
                <p>Name of Corvette Club:</p>
                <p>Date of car show/event:</p>
                <p>I am requesting the following (Remove the items you do not need or feel free to add to the list):</p>
                <ul>
                <li>Door Prizes/Raffle Items</li>
                <li>Goody Bags (please indicate how many bags you plan to distribute)</li>
                <li>Items to include in Goody Bags (please indicate how many bags you plan to distribute)</li>
                </ul>
                <p>Shipping address (please note we cannot deliver to a PO Box)</p>
                <p>Thank you,</p>
                <p><strong>The Entire Zip Corvette Team!</strong></p>
                <p>Why not share your clubs upcoming events. <a title="Share on G+" href="https://plus.google.com/u/0/b/117242916203293033004/communities/103340519547803937214/stream/a30f32ae-6062-4e11-8f73-11d32f965455" target="_blank" rel="noopener">Share on Google+ here</a>?</p>'
                            ),
                            'corvette-gifts.html' => array(
                                'title' => 'Corvette Gifts & Accessories',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Corvette Gift Ideas</span></p>
                <div>
                <p><img style="float: right;" title="Santa" src="http://peer1.zipproducts.netdna-cdn.com/media/wysiwyg/landing-pages/small-santa.png" alt="Santa">Need help finding the perfect gift for the Corvette enthusiast in your life? Look no further than Zip. From stocking stuffers to birthday presents to Father\'s Day gifts, we have more than 20,000 Corvette parts and accessories in stock and ready to ship.&nbsp; There\'s something for everyone! For the fan who wants to surround themselves with all things Corvette, we have <a title="Corvette Home and Office Decorations" href="http://www.zip-corvette.com/accessories/corvette-home-decorations.html">home and office decorations</a>, <a title="Corvette Books" href="http://www.zip-corvette.com/books-manuals.html">books</a>, <a title="Cell Phone Covers" href="http://www.zip-corvette.com/gift-ideas/stocking-stuffers/cell-phone-covers.html">cell phone covers</a>&nbsp;and <a title="Corvette Stools and Chairs" href="http://www.zip-corvette.com/accessories/corvette-stools-chairs.html">Corvette furniture</a>. For those who want to simply wear their pride, we have <a title="Corvette Hats" href="http://www.zip-corvette.com/apparel/corvette-hats-caps-beanies-visors.html">hats</a>, <a title="Corvette Jackets" href="http://www.zip-corvette.com/apparel/corvette-specialty-jackets.html">jackets</a>, <a title="Corvette Shirts" href="http://www.zip-corvette.com/search/category/apparel?q=shirt">shirts</a> and <a title="Corvette Sunglasses" href="http://www.zip-corvette.com/apparel/corvette-sunglasses.html">sunglasses</a>....just to name a few!&nbsp; No matter your favorite generation, we have the ideal Corvette gift for you to give or receive!</p>
                <div class="alert alert-danger hide">
                <h5>Orders shipping UPS Ground must be placed on or before Friday, December 16th to arrive before Christmas. Express delivery services are available through December 23rd, upgraded services (Next Day Air, 2nd Day Air, 3 Day Select) are available to choose upon checkout. If Saturday delivery is required you must <a title="Contact Us" href="http://www.zip-corvette.com/contacts">contact us</a> before 3pm EST after placing your order to let us know you want it delivered on Saturday.</h5>
                </div>
                <p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/9047" category_ids="9047,9048,9049,9050,9051,9052,9053,9054,9055,9056,9057,9058,9059,9060,9061,9062,9063,9064,9065,9066,9068,9069,9070,9071,9072,9073,9074,9075,9076,9077,9078,9079,9080,9081,9082,9083"}}</p>
                <h2>Never Go Wrong with Gift Cards</h2>
                <p>Not sure what would suit the Corvette enthusiast on your shopping list? A <a title="Zip Corvette Gift Card" href="http://www.zip-corvette.com/accessories/corvette-gift-cards.html">Zip Corvette gift card</a> is what you\'re looking for! When you give a gift card, you allow the recipient to get exactly what he wants! No more worrying about size, color or style. A Zip Corvette gift card is sure to be a hit<span style="color: #000000; font-family: Arial, Verdana, Arial, Helvetica, sans-serif, \'Trebuchet MS\', \'Lucida Grande\', \'Lucida Sans\'; font-size: 12px; line-height: normal;">.</span></p>
                <hr>
                <h2>Why Shop Zip for your Corvette Gifts?</h2>
                <p>You can rest easy when you shop with Zip. We have the best returns and exchanges policy in the industry. Our <a title="Our Guarantee" href="http://www.zip-corvette.com/our-guarantee">"No Hassle" Guarantee</a> means that we are committed to your satisfaction with every purchase, every time. With hundreds of Corvette gift ideas in-catalog and online, shopping for the perfect gift has never been easier.</p>
                <hr>
                <h2>See that your Gift Packages arrive on Time.</h2>
                <div class="row-fluid">
                <div class="span6"><img style="float: left;" title="Delivery Times" src="https://s3.amazonaws.com/zip-corvette/web/files/images/cms/ups_map_2016.gif" alt="Delivery Times" width="546" height="353"></div>
                <div class="span6">
                <p>No matter the occasion, your gift must arrive on time. Check our shipping map for transit times based on your location. We guarantee same-day shipping for every order of in-stock items placed by&nbsp;<span class="aBn" data-term="goog_1225691860"><span class="aQJ">3 pm EST</span></span>. For faster delivery, we offer "rush" services. Still not sure which shipping option is best for you? <a title="Contact Us" href="http://www.zip-corvette.com/contacts">Contact us</a> and we\'ll help you choose options that guarantee an on-time delivery.</p>
                </div>
                </div>
                </div>'
                            ),
                            'corvettes-and-coffee.html' => array(
                                'title' => 'Corvettes and Coffee',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p style="text-align: center;"><img title="Corvettes and Coffee" src="{{media url=&quot;landing-pages/coffee-2018-Recovered.jpg&quot;}}" alt="Corvettes and Coffee"></p>
                <p>&nbsp;</p>
                <div>
                <div>You\'re invited to join us for Corvettes &amp;&nbsp;Coffee here at Zip on Friday morning, Oct. 19 from 8-10am. Stop by on your way to work - or if you\'re lucky enough to have the day off, spend the morning with us!</div>
                <div>&nbsp;</div>
                <div>You bring the Corvettes, we\'ll bring the Coffee (and donuts, too)!&nbsp;</div>
                <div>&nbsp;</div>
                <div>This is our ONLY Corvettes&nbsp;&amp; Coffee event for the season, so don\'t miss out! No RSVP necessary.</div>
                <div>&nbsp;</div>
                <div>Zip Corvette</div>
                <div>8067 Fast Lane</div>
                <div>Mechanicsville, Va 23111</div>
                </div>'
                            ),
                            'corvettes-at-carlisle.html' => array(
                                'title' => 'Corvettes at Carlisle',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><img style="display: block; margin-left: auto; margin-right: auto;" title="Carlisle 2018" src="{{media url=&quot;landing-pages/carlisle-2018.jpg&quot;}}" alt="Carlisle 2018"></p>
                <p>The most popular sale of the year is back - Zip\'s annual Corvettes at Carlisle Sale!</p>
                <p>Place an online order of $99 or more now through Monday and you\'ll get 10% off + FREE SHIPPING*!&nbsp;Simply use promo code <strong><span style="color: #3366ff;">SHOWSALE18</span></strong> at checkout to receive your discount and free shipping!</p>
                <p>Shipping your order outside the 48 contiguous US? Use the promo code for your 10% discount. International orders do not qualify for free shipping.</p>
                <p><em>*Must use promo code: SHOWSALE18 to receive 10% discount on orders of $99 or more. Free shipping applies to orders delivered to any of the 48 contiguous US only. All oversize charges, crate fees and truck freight charges still apply. If an item is ordered with free shipping and is returned for a credit and was not damaged or defective upon customer receipt, the shipping charges for the item will be deducted from the total refund. Offer valid for retail customers only, must order online before 8/27/2018 at midnight ET. Not to be combined with any other discount or promotion. Some items marked accordingly do not qualify for the discount.</em></p>
                <p style="line-height: 19px;"><span style="font-size: 12pt;">Shop Popular Categories Below:</span></p>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="42"}}</p>
                <hr>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="43"}}</p>
                <hr>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="44"}}</p>
                <hr>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="45"}}</p>
                <hr>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="46"}}</p>
                <hr>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="47"}}</p>
                <hr>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="48"}}</p>'
                            ),
                            'customer-testimonials.html' => array(
                                'title' => 'Customer Testimonials',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>{{widget type="testimonials/widget_listFull" show_title="1"}}</p>'
                            ),
                            'discount-programs.html' => array(
                                'title' => 'Discount Program',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<div id="contentPart" class="contentpartblock">
                <p>Zip offers two order value discounts for customers that are buying a lot of Corvette parts to complete large restorations on 1953-1982 Corvettes:</p>
                <strong>10% Corvette Restorer\'s Discount </strong>
                <p>Retail customers that place a minimum purchase of $850 of any combination of Corvette parts and/or accessories receive 10% off the order. Shipping charges, core charges, and all other charges besides the actual price of the Corvette parts and accessories are not included in the order total to qualify for this discount. Items listed as "No Discount" may be included in the order total, but are not eligible for discount.</p>
                <strong>10% Restoration Club "VIP"</strong>
                <p>Retail customers that place a minimum purchase of $2000 of any combination of Corvette parts and/or accessories receive 10% off the order, PLUS an additional 10% off every order you place for the next year. Shipping charges, core charges, and all other charges besides the actual price of the parts and accessories are not included in the order total to qualify for this discount. Items listed as "No Discount" may be included in the order total, but are not eligible for discount.</p>
                <p>How It Works:</p>
                <p>Place an online order and the discount will be applied after the order is received by Zip Products. We review all orders to insure that proper discounting is applied before shipment. Initial discounts will not be shown on the website during checkout.</p>
                <span class="lead">Zip Products\' Jobber and Dealer Corvette Parts Discount Programs</span>
                <p>Zip Products offers two discount programs for easy qualifying, to any automobile related business. Presently, over 6,000 domestic and international Corvette customers enjoy a discount on their orders. Contact us and ask to speak to one of our sales representatives to see how you can qualify. We\'re serious about the care, maintenance, restoration and preservation of the Corvette. Our commitment to you is clear - we strive to offer you the best Corvette parts and service available. To qualify for either you must be an automobile related business and provide proof of business by furnishing Zip Products with a copy of one of the following prior to our shipping your initial discounted order:</p>
                <ul>
                <li class="first">Business License</li>
                <li>Federal I.D. Certificate</li>
                <li>State Tax Certificate</li>
                <li>Yellow page or newspaper display ad (not a classified ad)</li>
                <li class="last">For businesses located in Virginia, to be tax exempt, please provide your tax-exempt number. For both programs, there are Corvette parts in our catalog that are not eligible for discounts. These items will have an "ND" designation in the catalog. These Corvette parts can be included in the total order requirements to qualify the remaining parts for a discount. Our discount programs put each part into a pricing level - ND, 1, 2, 3, or 4. Upon being established as a Jobber or a Dealer, you will receive a pricing guide which lists all parts and their corresponding price level. Our two programs and their discount structures are as follows:</li>
                </ul>
                <ol style="list-style: decimal !important;">
                <li style="list-style: decimal !important;">Jobber: <br> No stocking order required, just a minimum of $100 (retail) initial order.<br> Call for discount structure</li>
                <li style="list-style: decimal !important;">Dealer<br> Must purchase a single stocking order of $750 (retail) of currently available Corvette parts. On your initial order of $750 you will receive the appropriate discounts on discountable parts.<br> Call for discount structure</li>
                </ol>
                <p>You can pay for your purchase by one of the methods listed below:</p>
                <ul>
                <li class="first">Prepayment by preprinted check or money order (U.S. Funds)</li>
                <li class="last">Credit Card (Visa, MasterCard, American Express, Discover Card.)</li>
                </ul>
                <p>Your ordering frequency will be reviewed annually to determine if you are continuing to operate as a dealer. Your Zip Dealer status will remain in effect if yearly purchases are made. For more information, <a href="/contacts">Contact us</a>.</p>
                </div>'
                            ),
                            'email-sign-up.html' => array(
                                'title' => 'Email Sign Up',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Sign up to receive our email newsletters</span></p>
                <!--<p><img title="Email Sign Up" alt="Email Sign Up" src="{{media url=&quot;wysiwyg/landing-pages/enter-to-win.jpg&quot;}}" /></p>-->
                <p>Get the latest parts info and offers delivered straight to your inbox when you subscribe to Zip\'s e-mail alerts!</p>
                <p>Our weekly e-newsletter gives you an inside look at what\'s new in our warehouse and what\'s breaking in Corvette technology. When you subscribe below, you can let us know which Corvettes interest you most and we\'ll send generation-specific news and special offers tailored to your Corvette(s).</p>
                <p>Rest assured, your contact information is safe with us. We do not share your e-mail address with outside vendors and you\'ll only receive e-mails from Zip. If at any point you wish to unsubscribe or update your Corvette preferences, you can do so with the simple click of a mouse.</p>
                <p>Become a Zip Corvette e-mail subscriber and stay on top of what\'s happening in the Corvette world!</p>
                <!--<p>Join Zip\'s e-mail list now through Friday, Jan. 2 and you\'ll be automatically entered to win a silver Corvette pendant! The C5 Z06 Corvette Emblem License Plate pendant is American craftsmanship at it\'s best.</p>
                <p>We have gold and silver pendants available for multiple generations. <a title="Jewelry" href="http://www.zip-corvette.com/accessories/corvette-jewelry.html">Shop all jewelry here</a>.</p>-->
                <p><strong>Join our mailing list using the form below.</strong></p>
                <p>{{widget type="cms/widget_block" template="cms/widget/static_block/default.phtml" block_id="50"}}</p>'
                            ),
                            'enter-success.html' => array(
                                'title' => 'Sign Up Success',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Thanks For Signing Up!</span></p>
                <p>As a loyal subscriber you will now have access to exclusive sales and more. Be sure to check your inbox often to take advantage of every offer.</p>'
                            ),
                            'fast-four.html' => array(
                                'title' => 'Fast Four Discounted Categories',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8898" category_ids="8777,8824,8898,8969"}}</p>
                <h3><strong>Shop these Fast Four categories and save 10% at checkout! No coupon codes necessary. Simply add any discountable item from one of the categories above to your shopping cart and a 10% discount will be applied to that item automatically.</strong></h3>
                <p><em>*10% discount is not to be combined with any other offer or promotion. Offer valid for retail customers only - wholesale accounts not eligible. Select non-discountable items not eligible.&nbsp;Discount offer ends 4/2/2017 at midnight EDT.</em></p>'
                            ),
                            'fathers-day-special.html' => array(
                                'title' => 'Father\'s Day Special',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Father\'s Day Special - Save 15% When Shopping Corvette accessories, apparel, books and manuals!</span></p>
                <p>Simply order before 06/18/2018 at midnight and use coupon code <span style="color: #ff6600;"><strong>DAD18</strong></span>&nbsp;to apply discount. <em>Discount only valid for items listed in categories below and may not be combined with any other offer or discount.</em></p>
                <p><em>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8318" category_ids="8318,8324,8335,8345,8354,8693,8361,8362,8363,8364,8365,9492,8366,8368,8369,8370,8371,8372,8375,8373,8377,8379,8380,8383,8384,8385,8386,8387,8388,8389,8391,8392,8394,8395,8396,8397,8398,8399,8401,8404,8406,8407,8414,8415,8416,8417,8418,8419,8420"}}</em></p>'
                            ),
                            'halloween-dress-up-sale.html' => array(
                                'title' => 'Halloween Dress-Up Sale',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><span class="lead">Halloween Dress-Up Sale</span></p>
                <table border="0">
                <tbody>
                <tr>
                <td style="font-family: \'PT Sans\', Arial, Helvetica, sans-serif; font-size: 10pt;"><img class="prod-main-image" style="vertical-align: top;" src="http://hosting-source.bm23.com/18315/public/newsletter_10292014/dress-up-main-image.jpg" alt="Halloween Dress Up" width="650" height="442"></td>
                </tr>
                </tbody>
                </table>
                <table border="0">
                <tbody>
                <tr>
                <td>&nbsp;</td>
                <td style="font-family: \'PT Sans\', Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 19px;"><br>
                <p style="line-height: 19px;"><span style="font-size: 11pt;"><strong>Dress-up yourself and your Vette with discounted apparel and engine compartment accessories!<br> <br> Through Monday, November 3rd at midnight EDT we will discount ALL apparel by 15% and ALL engine dress-up accessories by 10%.<br> <br> <span style="text-decoration: underline;">How to Qualify</span>: Enter source code <span style="color: #0163c0;">HA14</span> when checking out to receive 10% off new engine compartment accessories. No source code is required for apparel purchases, 15% discount will apply automatically.</strong><br> <br> <em>Offer valid for retail customers only. Select non-discountable items not eligible. Offer expires 11/3/14 at midnight EDT.</em></span></p>
                </td>
                <td>&nbsp;</td>
                </tr>
                </tbody>
                </table>
                <p>&nbsp;<img src="http://hosting-source.bm23.com/18315/public/background_images/bg_shadow.jpg" alt="">&nbsp;</p>
                <table border="0">
                <tbody>
                <tr>
                <td>&nbsp;</td>
                <td style="font-family: \'PT Sans\', Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 19px;"><a href="http://www.zip-corvette.com/halloween-dress-up-sale.html" target="_blank" rel="noopener"><img src="http://hosting-source.bm23.com/18315/public/newsletter_10292014/engine-dress-up-main.jpg" alt="Engine Dress Up"></a></td>
                <td>&nbsp;</td>
                <td style="font-family: \'PT Sans\', Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 19px;">
                <p style="line-height: 19px;"><span style="font-size: 11pt;"> <span style="font-size: 13pt;"><strong>Save 10% on Engine Dress Up - <span style="color: #0163c0;">Use Source Code HA14</span></strong></span><br> <br> <strong> Shop Engine Compartment Dress-Up Accessories:</strong></span></p>
                <ul>
                <li><a title="C4" href="http://www.zip-corvette.com/84-96-c4/engine-performance/corvette-engine-compartment-dress-up.html" target="_blank" rel="noopener">1984-1996 C4 Engine Dress-Up Accessories</a></li>
                <li><a title="C5" href="http://www.zip-corvette.com/97-04-c5/engine-performance/corvette-engine-compartment-dress-up.html" target="_blank" rel="noopener">1997-2004 C5 Engine Dress-Up Accessories</a></li>
                <li><a title="C6" href="http://www.zip-corvette.com/05-13-c6/engine-performance/corvette-engine-dress-up.html" target="_blank" rel="noopener">2005-2013 C6 Engine Dress-Up Accessories</a></li>
                <li><a title="C7" href="http://www.zip-corvette.com/2014-2015-c7/engine-dress-up.html" target="_blank" rel="noopener">2014-2014 C7 Engine Dress-Up Accessories</a></li>
                </ul>
                </td>
                <td>&nbsp;</td>
                </tr>
                </tbody>
                </table>
                <p>&nbsp;&nbsp;&nbsp;</p>
                <table border="0">
                <tbody>
                <tr>
                <td>&nbsp;</td>
                <td style="font-family: \'PT Sans\', Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 19px;"><a href="http://www.zip-corvette.com/apparel.html" target="_blank" rel="noopener"><img src="http://hosting-source.bm23.com/18315/public/newsletter_10292014/apparel-main.jpg" alt="Apparel"></a></td>
                <td>&nbsp;</td>
                <td style="font-family: \'PT Sans\', Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 19px;">
                <p style="line-height: 19px;"><span style="font-size: 11pt;"><strong><span style="font-size: 13pt;"><strong>Save 15% on Apparel - <span style="color: #0163c0;">Discount Applies Automatically</span></strong></span><br> <br> Shop Discounted Vette Apparel and save 15%:</strong></span> <span style="text-align: center; font-size: 16pt;"><br> <br> <br> <a title="All Apparel" href="http://www.zip-corvette.com/apparel.html" target="_blank" rel="noopener">Shop All Apparel Now &gt;&gt;</a> </span><br><br> <br> <a title="Corvette Racing" href="http://www.zip-corvette.com/apparel/corvette-racing.html" target="_blank" rel="noopener">Corvette Racing</a> | <a title="Hats and Caps" href="http://www.zip-corvette.com/apparel/corvette-hats-caps-beanies-visors.html" target="_blank" rel="noopener">Hats &amp; Caps</a> | <a title="Grand Sport" href="http://www.zip-corvette.com/apparel/grand-sport-corvette-apparel.html" target="_blank" rel="noopener">Grand Sport</a> | <a title="ZR1" href="http://www.zip-corvette.com/apparel/zr1-corvette-apparel.html" target="_blank" rel="noopener">ZR1</a> | <a title="Z06" href="http://www.zip-corvette.com/apparel/z06-corvette-apparel.html" target="_blank" rel="noopener">Z06</a> | <a title="C7" href="http://www.zip-corvette.com/apparel/7th-generation-corvette.html" target="_blank" rel="noopener">C7</a></p>
                </td>
                <td>&nbsp;</td>
                </tr>
                </tbody>
                </table>'
                            ),
                            'hours-of-operation' => array(
                                'title' => 'Hour of Operation',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<h2>Call Center Hours</h2>
                <p>Zip Corvette sales and customer service representatives are here to serve you during the following hours:</p>
                <ul class="unstyled">
                <li>Mon-Thu. 8:30am-8pm</li>
                <li>Friday 8:30am-5:30pm</li>
                <li>Saturday 10am-3pm</li>
                </ul>
                <p>(Eastern Standard Time)</p>
                <hr>
                <p>Our showroom is open to the public Monday-Friday from 8:30am-5:30pm (EST). We take orders in our showroom or you may call your order in over the phone and we will prepare your items in advance so you do not have to wait.</p>
                <hr>
                <p>Our performance and installation facility is open Monday-Friday from 8:30am-5:30pm (EST). Visit our showroom to setup an appointment to have your Corvette tuned, accessorized, enhanced or more. Read more about or <a title="Zip Corvette Installs" href="/we-install-corvette-parts">performance and installation facility here</a>. *All work requires an appointment.<br><br>Other questions? <a title="Contact Us" href="/contacts">Contact us here</a>&nbsp;24 hours a day 7 days a week.</p>
                <p><img title="Zip Corvette Building" src="{{media url=&quot;zip-corvette-hours-of-operation.jpg&quot;}}" alt="Zip Corvette Building"></p>'
                            ),
                            'independence-day-flat-rate-shipping.html' => array(
                                'title' => 'Independence Day Flat Rate Shipping',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p><img style="display: block; margin-left: auto; margin-right: auto;" title="4th Flat Rate Shipping" src="{{media url=&quot;wysiwyg/landing-pages/4th-flat-rate-2017.jpg&quot;}}" alt="4th Flat Rate Shipping"></p>
                <p><span style="font-size: medium;">Happy Birthday America! We\'re celebrating 242 years of Independence with Flat Rate Shipping on all online orders!</span></p>
                <p><span style="font-size: medium;">This holiday weekend is a great time to stock up on your favorite Corvette Parts and Accessories - and we\'ll ship it all to your door for a low flat rate of $7.95! Simply select the Flat Rate Shipping option at checkout to enjoy your savings.</span></p>
                <p><span style="font-size: small;"><em>$7.95 Flat rate shipping only available to orders shipped to the 48 contiguous United States. All oversize charges, crate fees and truck freight charges still apply. Offer valid on orders placed before 07/06/2018 at midnight ET for retail customers only - wholesale and VIP accounts not eligible. Offer not to be combined with any other discount or promotion.</em></span></p>
                <p><span style="font-size: medium;">&nbsp;</span></p>
                <p><span style="font-size: small;"><em>{{widget type="levcore/catalog_category_widget_grid" category_level="category/8716" category_ids="8354,8693,8366,8368,8369,8372,8378,8384,8392,8756,8771,8825,8895,8924,8962,8971"}}</em></span></p>'
                            ),
                            'liability-disclaimer' => array(
                                'title' => 'Liability Disclaimer',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>Zip Products is not responsible for any damage, injury, loss or any other charges, caused by delays or incurred in the replacement of any defective items. Zip Products\' liability is limited to the replacement and the shipping cost of defective items only. Improper installation, damage or injury resulting from the use of items sold by Zip Products are the purchaser\'s responsibility. Due to changes in the Corvette industry, part numbers, specifications, prices and availability of parts can change. Consequently, there may be times when parts are discontinued or prices increased. Changes may also occasionally occur in our policies and terms. Call Zip Products during regular business hours for up-to-date information.</p>'
                            ),
                            'memorial-day.html' => array(
                                'title' => 'Memorial Day Flat Rate Shipping',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<div style="text-align: center;"><img title="Memorial Day Flat Rate Shipping" src="{{media url=&quot;landing-pages/memorial-day-landing-page-2018.jpg&quot;}}" alt="Memorial Day Flat Rate Shipping"></div>
                <div style="text-align: center;">&nbsp;</div>
                <div style="text-align: center;">&nbsp;</div>
                <div>Zip Corvette\'s annual Memorial Day Sale is back!</div>
                <div>&nbsp;</div>
                <div>Take advantage of $7.95 Flat Rate Shipping when you order online this holiday weekend. Simply select Flat Rate Shipping option at checkout.</div>
                <div>&nbsp;</div>
                <div>From brakes to bumpers and carpet to calipers, we have what you need for a summer of Corvette fun.</div>
                <div>&nbsp;</div>
                <div>As you gather with friends and family this weekend to celebrate the unofficial start of summer, we encourage you to pause and remember those who have made the ultimate sacrifice while defending this great nation. The reason why we are the Land of the Free is because we are also the Home of the Brave!</div>
                <div>&nbsp;</div>
                <div><em>*Flat rate shipping only available to orders shipped to the 48 contiguous United States. All oversize and truck freight charges still apply. Retail customers only. Online orders only. Must select Flat Rate Shipping option at checkout. All offers expire midnight ET Monday, May 28. Offer valid for retail customers only, must order online before 5/28/2018 at midnight ET. Not to be combined with any other discount or promotion.</em></div>'
                            ),
                            'new-website-faq' => array(
                                'title' => 'New Website FAQs',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>We have made major changes to our website, all in an effort to make your shopping experience better than ever. We recognize that some questions will arise during your visit to our website and have answered some of the most frequently ask questions. Feel free to <a title="Contact Us" href="/contacts/">email us here</a> or call us if there is anything else we can help you with.</p>
                <div id="new-faq">
                <div class="form-sub-header"><a href="#">1. I am having trouble logging in to my account. Does my login and password still work?</a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>No. In order to protect your privacy, we did not copy your password to our new website. If you have an e-account that you use to place orders, review history, view pricing or more at our online store, please <a title="Password Reset" href="/customer/account/login/?reset-password=true">reset your password</a> here.</li>
                </ul>
                </div>
                <div class="form-sub-header"><a href="#">2. Can I see my order history?</a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>Once you have signed into your e-account you will be able to <a title="Order History" href="/sales/order/history">view past order history</a>&nbsp;of orders that you placed online. If you placed an order over the phone or by any other method other than our website, that order will not be available for viewing from within your account history. You will need to contact us for details specific to non-website placed orders.</li>
                </ul>
                </div>
                <div class="form-sub-header"><a href="#">3. The website said my item was In Stock but my order confirmation says it is on backorder. Which status is correct? </a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>If you see a green <span class="label label-success">In Stock</span> message next to any item on our website then in most cases that item is in stock and ready to ship from our warehouse. There are rare instances where our website says an item is In Stock but the order confirmation says the item is on backorder. This is due to the fact that our inventory levels are updated periodically on our website, not up to the latest minute. Your order confirmation takes precedence over item availability on our website and should be used as the main resource for item shipping status.</li>
                </ul>
                </div>
                <div class="form-sub-header"><a href="#">4. Can I track my order once it has shipped?</a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>Any order that ships via UPS Ground can be tracked from our website once it has been closed. You must log into your e-account, navigate to your order history, click on the order in question and just below your shipping and billing information you will see a link that says "Track Your Order". Click the link to open your tracking information.</li>
                </ul>
                </div>
                <div class="form-sub-header"><a href="#">5. Will my shopping cart time out and be emptied after a certain amount of time?</a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>If you are not logged into your e-account the items will be removed from your shopping cart as soon as you close your browser. If you are logged into your e-account the items added to your shopping cart will remain there for one year IF you check the "Remember Me" box when you logged in. Checking "Remember Me" will create a persistent cookie that enables your shopping cart to be saved for your next logged-in session.</li>
                </ul>
                </div>
                <div class="form-sub-header"><a href="#">6. I have a wholesale account. Will my discounted pricing be displayed on the website?</a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>If you are logged into your account your wholesale pricing will be displayed and applied to your order.</li>
                </ul>
                </div>
                <div class="form-sub-header"><a href="#">7. I am tax exempt. Will I be charged tax when ordering online?</a></div>
                <div class="form-body">
                <ul class="filter-list">
                <li>If you are a Virginia customer and have submitted the proper tax exempt forms you will not be charged tax when ordering online.</li>
                </ul>
                </div>
                </div>'
                            ),
                            'ordering-methods' => array(
                                'title' => 'Ordering Methods',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<h2>How to Order</h2>
                <h4>Over the Phone</h4>
                <p>Call toll-free 1-800-962-9632 to speak to a Zip sales representative during regular business hours: 8:30 a.m. to 8 p.m. EST Monday-Thursday, 8:30 to 5:30 on Friday and 10 to 3 Saturdays. (Toll-free call area includes Alaska, Canada, Hawaii, Puerto Rico and Virgin Islands.) Outside the U.S. please call 804-746-2290 to place an order or receive help.</p>
                <h4>Through our 24-hour Fax Line</h4>
                <p>Fax an order anytime to 804-730-7043. <a title="Fax Order Form" href="https://s3.amazonaws.com/zip-corvette/web/misc-documents/zip-order-form-web_rev+apr12.pdf" target="_blank" rel="noopener">Download an order form here</a>. Fax orders are processed within 24 hours of receipt.</p>
                <h4>On our Website</h4>
                <p>Order quickly and securely through our website at <a href="{{store url=\'\'}}">www.zip-corvette.com</a>.</p>
                <h4>By Mail</h4>
                <p>Mail-in orders are processed within 24 hours of receipt. <a title="Mail In Order Form" href="https://s3.amazonaws.com/zip-corvette/web/misc-documents/zip-order-form-web_rev+apr12.pdf" target="_blank" rel="noopener">Download an order form here</a>.</p>
                <h4>In our Showroom</h4>
                <p>Zip strives to have all parts and accessories available for your immediate pick-up at our showroom counter. Call before you come and we\'ll have your order ready and waiting. Showroom hours are 8:30 a.m. to 5:30 p.m. EST Monday-Friday and 10 to 3 Saturdays.</p>
                <h2>Payment Methods</h2>
                <h4>Credit Cards</h4>
                <p>Zip accepts VISA, MasterCard, Discover Card and American Express. Please provide the card number, expiration date, issuing bank and cardholder\'s name, billing address and daytime phone number. For fax and mail-in orders, be sure the order form includes an authorized signature.</p>
                <h4>Personal Check</h4>
                <p>Checks must be first party only and preprinted with a valid name and address. Money orders, cashiers checks and certified checks are also accepted, in U.S. funds only drawn on a U.S. bank. Check orders are processed same day as received. Checks returned due to insufficient funds will be charged a $20.00 handling fee.</p>
                <h4>Gift Cards</h4>
                <p>Surprise your favorite Corvette enthusiast with a gift certificate for merchandise from Zip\'s catalogs or on-line store. Redeemable through phone, web and mail orders, gift cards can be purchased by phone, fax or mail order only. Simply call and provide us the recipient\'s name and address, your name, address and daytime phone, the dollar amount and your choice of payment. We do the rest!</p>
                <h2>Other Ordering Information</h2>
                <h4>Sales Tax</h4>
                <p>Any order being shipped to an address in the state of Virginia must have 5% added to the total parts order for sales tax.</p>
                <h4>Liability Disclaimer</h4>
                <p>Zip Products is not responsible for any damage, injury, loss or any other charges, caused by delays or incurred in the replacement of any defective items. Zip\'s liability is limited to the replacement and the shipping cost of defective items only. Improper installation, damage or injuries resulting from the use of items sold by Zip Products are the purchaser\'s responsibility. Due to changes in the Corvette industry, part numbers, specifications, prices and availability of parts can change. Consequently, there may be times when parts are discontinued or prices increased. Changes may also occasionally occur in our policies and terms. Call Zip during regular business hours for up-to-date information.</p>
                <h2>Receipt of Order</h2>
                <p>We ask that you please inspect all merchandise immediately upon receipt of order. For information about returns, exchanges, missing items, damaged items or any other related order discrepancy, please visit our <a title="Returns and Exchanges" href="{{store url=\'\'}}">Return and Exchanges</a> page or <a title="Contact Us" href="{{store url=\'\'}}">Contact us</a> by phone or email as soon as possible.</p>'
                            ),
                            'our-guarantee' => array(
                                'title' => 'Our Guarantee',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<h2>Zip Products Low Price Guarantee</h2>
                <p>Zip Products works hard to make sure our prices are competitive, but occasionally, a competitor may offer a lower price. If you find the same brand part or accessory advertised for a lower price with another supplier, we\'ll match their current price. Price must be published in a current magazine advertisement, catalog or website, and does not apply to closeouts or liquidations. Call 1-800-962-9632 for price match service. Don\'t sacrifice quality and customer service just for a few dollars. Give Zip a call.</p>
                <h2>No-Hassle Guarantee</h2>
                <p>Since 1977, Zip Products has supplied Corvette Parts and Accessories to enthusiasts the world over. Then as now, packed into every shipment is our unparalleled commitment to customer service. Here at Zip, we believe a happy customer is a repeat customer. If you\'re not completely satisfied with any product for any reason, we\'ll promptly refund or exchange any purchase within 30 days. <a title="Contact Us" href="/contacts">Contact us</a> by phone or email for details.</p>
                <h2>We don\'t charge upfront for backorders!</h2>
                <p>We\'re proud that over 96% of all orders are boxed and out the door within 24 hours of receipt. However, backorders do occasionally occur. Unlike other companies, Zip <strong>does not</strong> charge you up front for backorders; you are charged only when your items ship from our warehouse. Most backorders ship within 10 business days. For longer delays, our policy is to ship orders up to 30 days old. After 30 days we\'ll contact you for shipment authorization before shipping your order. If you wish to cancel a backorder, please <a title="Contact Us" href="/contacts">Contact us</a> by phone or email as soon as possible.</p>
                <h2>Shipping - Fast. Reliable. Affordable!</h2>
                <p>There is no reason to pay for "Rush" or "Same Day Processing" charges; Zip will ship your order the same day for no additional charge. Simply place your order by 3:00 PM EST Monday-Friday and all <strong>in stock</strong> items will ship from our Virginia location (zip code: 23111) the same day. A shipping confirmation with carrier and tracking information will be sent via e-mail as soon as your order has been shipped. Back-ordered items will ship at a later date, please see our <a title="Backorders" href="/shipping#backorders">backorder policies</a> for details. Products that ship from alternate warehouses will require additional time for delivery, please see our <a title="Dropships" href="/shipping#dropships">dropship policies</a> for details.</p>
                <h2 id="video">Who We Are ~ What We\'re All About</h2>
                <p>Watch our five minute video featuring David Walker, President of Zip Corvette and a number of the Zip faithful as we show you the faces behind the Zip logo.</p>
                <div class="embed-container"><iframe src="//www.youtube.com/embed/UuDbDfBP-f8" width="320" height="240"></iframe></div>'
                            ),
                            'our-story.html' => array(
                                'title' => 'About Zip Products',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>&nbsp;</p>
                <div class="wistia_responsive_padding" style="padding: 56.25% 0 28px 0; position: relative;">
                <div class="wistia_responsive_wrapper" style="height: 100%; left: 0; position: absolute; top: 0; width: 100%;">
                <div class="wistia_embed wistia_async_nneez1t80p videoFoam=true" style="height: 100%; width: 100%;">&nbsp;</div>
                </div>
                </div>
                <p><br><br>The history of Zip Corvette has all the markings of the American Dream. In 1977, Wayne Walker, a Corvette owner and enthusiast, needed a gas line for his 1961 Corvette. A friend showed him how to bend steel tubing for the line and Walker\'s handiwork with a flaring tool was quickly noticed by other Corvette friends who began requesting that he make fuel lines for them. Virtually overnight, Zip Products was in business.&nbsp;</p>
                <div>What began as a part-time hobby headquartered in Walker\'s garage quickly grew into a thriving business. Hitting the road to attend every swap meet and Corvette show possible, Walker placed himself - and his products - directly in front of his customer base. During the first 10 years, Zip\'s single page list of inventory grew into a 128-page full color catalog. The Corvette aftermarket was taking its first steps and Zip was leading the way.</div>
                <div>&nbsp;</div>
                <div>Along the way, Walker\'s passion for, and knowledge of, Corvettes steadily grew as he personally restored some of the rarest Corvettes ever produced: a 1963 Z06 Big Tank Coupe, a 1967 L88 Convertible, a 1969 427 ZL1 and a host of fuel-injection, 396 and 427 Corvettes. As he set out to find the parts he needed to restore his own Corvettes, Walker was committed to making those parts widely available for other restorers, placing Zip at the forefront of the Corvette restoration hobby.</div>
                <div>&nbsp;</div>
                <div>A brick and mortar address with walk-in and mail order capabilities was the next logical step, and Zip eventually found it\'s home at 8067 Fast Lane in Mechanicsville, Virginia, nestled right outside of the Richmond city limits. In 1994, David Walker, Wayne\'s son, gained the reigns of leadership and set about building the company into one of the industry\'s leading purveyors of Corvette parts and performance accessories.</div>
                <div>&nbsp;</div>
                <div>Today, Zip\'s warehouse includes more than 25,000 of the newest-available and best-quality Corvette parts to be had. Zip\'s inventory is showcased in six different, generation-specific catalogs that often find a shelf life right alongside the manuals and guidebooks in a Corvette owner\'s garage. From early model restorers to sixth generation racers, fans of America\'s Sports Car rely on Zip to keep their beloved Corvettes on the open road.</div>
                <div>&nbsp;</div>
                <div style="padding: 15px; background-color: #eff3fb; border: 1px dashed #666; margin: 0px auto; float: left;">When placing a call into our sales team know that you are not just speaking with an "order taker" but someone who is knowledgable about Corvettes. This group of Corvette parts professionals has accumulated over 100 years of Corvette experience, which they are glad to share with you while you work to restore or enhance your Corvette. Need help with something? <a title="Contact Us" href="/contacts">Contact Us Here</a> or call us toll free at 1-800-962-9632. Zip: Corvette are all we do.</div>
                <p>&nbsp;</p>'
                            ),
                            'paypal-credit.html' => array(
                                'title' => 'PayPal Credit',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<h2>What is PayPal Credit?</h2>
                <p>PayPal Credit is a line of credit from Comenity Capital Bank that gives you the flexibility to pay for your purchase now, or pay over time. It’s easy to apply and easy to use.</p>
                <h4 id="1" class="collapse">How does PayPal Credit work?</h4>
                <div class="faq_answer">
                <p>You can use PayPal Credit on purchases at thousands of stores that accept PayPal.</p>
                <p>Here\'s how:</p>
                <ul>
                <li><span class="li_content">Check out with PayPal and then choose PayPal Credit.</span></li>
                <li><span class="li_content">Answer 2 quick questions and accept the terms.</span></li>
                <li><span class="li_content">You’ll know within seconds if you are approved.</span></li>
                <li><span class="li_content">Look for your PayPal Credit statement via email.</span></li>
                </ul>
                <p>If your PayPal Credit account is linked to your PayPal account, your offers may vary.</p>
                <h4 id="2" class="collapse">How do I start using PayPal Credit?</h4>
                <div class="faq_answer">
                <p>Select PayPal Credit as your payment choice during checkout. You’ll be asked to provide your date of birth and the last 4 digits of your Social Security number, and then to agree to the terms and conditions, to apply for a PayPal Credit account. You’ll know within seconds if you are approved.</p>
                <h4 id="3" class="collapse">How do I select PayPal Credit as my payment source?</h4>
                <div class="faq_answer">
                <p>There are two ways to choose PayPal Credit. Zip Corvette has a PayPal Credit option at checkout. You can simply choose that option. If you don’t see a PayPal Credit option, check out with PayPal, and then you’ll have the option to select PayPal Credit as your payment method. Either way a window will appear on your screen where you’ll provide your date of birth and the last 4 digits of your Social Security number, and then agree to the terms and conditions. Approval takes just seconds and you can complete your purchase.</p>
                <p><a title="Paypal Credit" href="https://www.paypalcredit.com/index.html" target="_blank" rel="noopener">For more information about PayPal Credit click here</a>.</p>
                <p>&nbsp;</p>
                </div>
                </div>
                </div>'
                            ),
                            'request-catalog.html' => array(
                                'title' => 'Request a Catalog',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<div>
                <p>What began as a few photocopied loose sheets has evolved into generation specific, full-color Corvette parts catalogs we believe are the best out there. For the restoration crowd, we\'ve taken a fresh approach: build a book same as one would build a Corvette. More than just hundreds of Corvette part listings, photos, and helpful reference illustrations, you\'ll find everything presented in an easy-to-follow order that mimics the logical progression of a bare frame build-up. C5, C6 and C7-era enthusiasts know Zip\'s Corvette parts catalogs to be an exciting mix of interior, exterior, and underhood enhancements, performance bolt-ons, and Corvette-theme apparel and accessories. If you don\'t see what you\'re looking for in any edition, call or shop our Web store – we\'re adding new parts and accessories all the time!</p>
                </div>
                <div class="alert alert-info">
                <h3>Download our Parts Catalogs now:</h3>
                <p>We ship catalogs once a week; so you don\'t have to wait long to start putting your parts list together. However, we know some of you don\'t want to wait for the mail. We have our catalogs available in .pdf format below. You will need the free program <a title="Adobe Reader" href="http://get.adobe.com/reader/" target="_blank" rel="noopener">Adobe Acrobat Reader</a> to view them.</p>
                <p><a title="Download C1 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/53-62-c1-parts-catalog.pdf" target="_blank" rel="noopener">53-62 C1 Corvette</a> | <a title="Download C2 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c2-parts-catalog.pdf" target="_blank" rel="noopener">63-67 C2 Corvette</a> | <a title="Download C3 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/68-82-parts-catalog.pdf" target="_blank" rel="noopener">68-82 C3 Corvette</a> | <a title="Download C4 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c4-parts-catalog.pdf" target="_blank" rel="noopener">84-96 C4 Corvette</a> | <a title="Download Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c5-parts-catalog.pdf" target="_blank" rel="noopener">97-04 C5 Corvette</a> | <a title="Download C6 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c6-parts-catalog.pdf" target="_blank" rel="noopener">05-13 C6 Corvette</a>&nbsp;| <a title="Download C7 Catalog" href="https://s3.amazonaws.com/zip-corvette/web/catalogs/c7-corvette-parts-catlog-zip.pdf" target="_blank" rel="noopener">14-18 C7 Corvette</a></p>
                </div>
                <div class="row-fluid form-tower"><form style="float: left;" action="http://email.zip-corvette.com/public/webform/process/" method="post"><input name="fid" type="hidden" value="82pnbkz8mf2anftcgelt2l3t5j8b5"> <input name="sid" type="hidden" value="2cdb8ef4bb49bd5a3a5c35c299d7a019"> <input name="delid" type="hidden" value=""> <input name="subid" type="hidden" value=""> <input name="td" type="hidden" value=""> <input name="formtype" type="hidden" value="addcontact">
                <div id="row_3783" class="span11">
                <h2 style="margin: 12px 0;">PLEASE SELECT ONE OR MORE CATALOGS:</h2>
                </div>
                <div id="row_3781" class="span11">
                <div id="column_4883" class="span4" style="text-align: center;"><img title="C1 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c1-parts-catalog.jpg" alt="C1 Corvette Parts Catalog" width="94" height="125">
                <div class="field_block">
                <div class="checkbox field"><input id="field_64625" class="checkbox" style="float: none; display: inline-block;" name="10131[64625]" type="checkbox" value="1"> <label id="caption_64625" class="caption" style="float: none; display: inline-block;" for="field_64625"> 53-62 </label>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div id="column_4884" class="span4" style="text-align: center;"><img title="C2 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c2-parts-catalog.jpg" alt="C2 Corvette Parts Catalog" width="94" height="125">
                <div class="field_block">
                <div class="checkbox field"><input id="field_64631" class="checkbox" style="float: none; display: inline-block;" name="10133[64631]" type="checkbox" value="1"> <label id="caption_64631" class="caption" style="float: none; display: inline-block;" for="field_64631"> 63-67 </label>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div id="column_4885" class="span4" style="text-align: center;"><img title="C3 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c3-parts-catalog.jpg" alt="C3 Corvette Parts Catalog" width="94" height="125">
                <div class="field_block">
                <div class="checkbox field"><input id="field_64632" class="checkbox" style="float: none; display: inline-block;" name="10135[64632]" type="checkbox" value="1"> <label id="caption_64632" class="caption" style="float: none; display: inline-block;" for="field_64632"> 68-82 </label>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                <div id="row_3782" class="span11">
                <div id="column_4886" class="span4" style="text-align: center;"><img title="C4 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c4-parts-catalog.jpg" alt="C4 Corvette Parts Catalog" width="94">
                <div class="field_block">
                <div class="checkbox field"><input id="field_64633" class="checkbox" style="float: none; display: inline-block;" name="10137[64633]" type="checkbox" value="1"> <label id="caption_64633" class="caption" style="float: none; display: inline-block;" for="field_64633"> 84-96 </label>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div id="column_4887" class="span4" style="text-align: center;"><img title="C5 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c5-parts-catalog.jpg" alt="C5 Corvette Parts Catalog" width="94" height="125">
                <div class="field_block">
                <div class="checkbox field"><input id="field_64634" class="checkbox" style="float: none; display: inline-block;" name="10139[64634]" type="checkbox" value="1"> <label id="caption_64634" class="caption" style="float: none; display: inline-block;" for="field_64634"> 97-04 </label>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div id="column_4888" class="span4" style="text-align: center;"><img title="C6 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c6-parts-catalog.jpg" alt="C6 Corvette Parts Catalog" width="94" height="125">
                <div class="field_block">
                <div class="checkbox field"><input id="field_64635" class="checkbox" style="float: none; display: inline-block;" name="10141[64635]" type="checkbox" value="1"> <label id="caption_64635" class="caption" style="float: none; display: inline-block;" for="field_64635"> 05-13 </label>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                <div id="row_19381" class="span11">
                <div id="column_24424" class="span4" style="text-align: center;"><img title="C7 Corvette Parts Catalog" src="https://s3.amazonaws.com/zip-corvette/web/catalogs/c7-parts-catalog.jpg" alt="C7 Corvette Parts Catalog" width="94" height="125">
                <div class="field_block">
                <div class="checkbox field"><input id="field_213448" class="checkbox" style="float: none; display: inline-block;" name="52146[213448]" type="checkbox" value="1"> <label id="caption_213448" class="caption" style="float: none; display: inline-block;" for="field_213448"> 14-18&nbsp;</label>
                <div class="field_error">&nbsp;</div>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                </div>
                <div id="row_17661" class="span11">
                <div id="column_22242" class="span11">
                <div class="field_block">
                <div id="caption_63353" class="caption">First Name <span class="required">*</span></div>
                <div class="field"><input id="field_63353" class="text field" name="46669[63353]" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div class="field_block">
                <div id="caption_63354" class="caption">Last Name <span class="required">*</span></div>
                <div class="field"><input id="field_63354" class="text field" name="46670[63354]" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div class="field_block">
                <div id="caption_65180" class="caption">Street <span class="required">*</span></div>
                <div class="field"><input id="field_65180" class="text field" name="46671[65180]" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div class="field_block">
                <div id="caption_65182" class="caption">City <span class="required">*</span></div>
                <div class="field"><input id="field_65182" class="text field" name="46672[65182]" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div class="field_block">
                <div id="caption_65186" class="caption">State or Province <span class="required">*</span></div>
                <div class="field"><input id="field_65186" class="text field" name="46673[65186]" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div class="field_block">
                <div id="caption_65185" class="caption">ZIP Code <span class="required">*</span></div>
                <div class="field"><input id="field_65185" class="text field" name="46674[65185]" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div class="field_block">
                <div id="caption_65183" class="caption">Country <span class="required">*</span></div>
                <div class="field"><select id="field_65183" class="select field" name="46675[65183]">
                <option value="af">Afganistan</option>
                <option value="al">Albania</option>
                <option value="dz">Algeria</option>
                <option value="as">American Samoa</option>
                <option value="ad">Andorra</option>
                <option value="ao">Angola</option>
                <option value="ai">Anguilla</option>
                <option value="aq">Antarctica</option>
                <option value="ag">Antigua and Barbuda</option>
                <option value="ar">Argentina</option>
                <option value="am">Armenia</option>
                <option value="aw">Aruba</option>
                <option value="au">Australia</option>
                <option value="at">Austria</option>
                <option value="az">Azerbaijan</option>
                <option value="bs">Bahamas</option>
                <option value="bh">Bahrain</option>
                <option value="bd">Bangladesh</option>
                <option value="bb">Barbados</option>
                <option value="by">Belarus</option>
                <option value="be">Belgium</option>
                <option value="bz">Belize</option>
                <option value="bj">Benin</option>
                <option value="bm">Bermuda</option>
                <option value="bt">Bhutan</option>
                <option value="bo">Bolivia</option>
                <option value="ba">Bosnia and Herzegowina</option>
                <option value="bw">Botswana</option>
                <option value="bv">Bouvet Island</option>
                <option value="br">Brazil</option>
                <option value="io">British Indian Ocean Territory</option>
                <option value="bn">Brunei Darussalam</option>
                <option value="bg">Bulgaria</option>
                <option value="bf">Burkina Faso</option>
                <option value="bi">Burundi</option>
                <option value="kh">Cambodia</option>
                <option value="cm">Cameroon</option>
                <option value="ca">Canada</option>
                <option value="cv">Cape Verde</option>
                <option value="ky">Cayman Islands</option>
                <option value="cf">Central African Republic</option>
                <option value="td">Chad</option>
                <option value="cl">Chile</option>
                <option value="cn">China</option>
                <option value="cx">Christmas Island</option>
                <option value="cc">Cocos Keeling Islands</option>
                <option value="co">Colombia</option>
                <option value="km">Comoros</option>
                <option value="cg">Congo</option>
                <option value="cd">Congo, Democratic Republic of the</option>
                <option value="ck">Cook Islands</option>
                <option value="cr">Costa Rica</option>
                <option value="ci">Cote d\'Ivoire</option>
                <option value="hr">Croatia Hrvatska</option>
                <option value="cu">Cuba</option>
                <option value="cy">Cyprus</option>
                <option value="cz">Czech Republic</option>
                <option value="dk">Denmark</option>
                <option value="dj">Djibouti</option>
                <option value="dm">Dominica</option>
                <option value="do">Dominican Republic</option>
                <option value="tp">East Timor</option>
                <option value="ec">Ecuador</option>
                <option value="eg">Egypt</option>
                <option value="sv">El Salvador</option>
                <option value="gq">Equatorial Guinea</option>
                <option value="er">Eritrea</option>
                <option value="ee">Estonia</option>
                <option value="et">Ethiopia</option>
                <option value="fk">Falkland Islands Malvinas</option>
                <option value="fo">Faroe Islands</option>
                <option value="fj">Fiji</option>
                <option value="fi">Finland</option>
                <option value="fr">France</option>
                <option value="fx">France, Metropolitan</option>
                <option value="gf">French Guiana</option>
                <option value="pf">French Polynesia</option>
                <option value="tf">French Southern Territories</option>
                <option value="ga">Gabon</option>
                <option value="gm">Gambia</option>
                <option value="ge">Georgia</option>
                <option value="de">Germany</option>
                <option value="gh">Ghana</option>
                <option value="gi">Gibraltar</option>
                <option value="gr">Greece</option>
                <option value="gl">Greenland</option>
                <option value="gd">Grenada</option>
                <option value="gp">Guadeloupe</option>
                <option value="gu">Guam</option>
                <option value="gt">Guatemala</option>
                <option value="gn">Guinea</option>
                <option value="gw">Guinea-Bissau</option>
                <option value="gy">Guyana</option>
                <option value="ht">Haiti</option>
                <option value="hm">Jamaica</option>
                <option value="va">Holy See (Vatican City State)</option>
                <option value="hn">Honduras</option>
                <option value="hk">Hong Kong</option>
                <option value="hu">Hungary</option>
                <option value="is">Iceland</option>
                <option value="in">India</option>
                <option value="id">Indonesia</option>
                <option value="ir">Iran, Islamic Republic of</option>
                <option value="iq">Iraq</option>
                <option value="ie">Ireland</option>
                <option value="il">Israel</option>
                <option value="it">Italy</option>
                <option value="jp">Japan</option>
                <option value="jo">Jordan</option>
                <option value="kz">Kazakhstan</option>
                <option value="ke">Kenya</option>
                <option value="ki">Kiribati</option>
                <option value="kp">Korea, Democratic People\'s Republic of</option>
                <option value="kr">Korea, Republic of</option>
                <option value="kw">Kuwait</option>
                <option value="kg">Kyrgyzstan</option>
                <option value="la">Lao People\'s Democratic Republic</option>
                <option value="lv">Latvia</option>
                <option value="lb">Lebanon</option>
                <option value="ls">Lesotho</option>
                <option value="lr">Liberia</option>
                <option value="ly">Libyan Arab Jamahiriya</option>
                <option value="li">Liechtenstein</option>
                <option value="lt">Lithuania</option>
                <option value="lu">Luxembourg</option>
                <option value="mo">Macau</option>
                <option value="mk">Macedonia, The Former Yugoslav Republic of</option>
                <option value="mg">Madagascar</option>
                <option value="mw">Malawi</option>
                <option value="my">Malaysia</option>
                <option value="mv">Maldives</option>
                <option value="ml">Mali</option>
                <option value="mt">Malta</option>
                <option value="mh">Marshall Islands</option>
                <option value="mq">Martinique</option>
                <option value="mr">Mauritania</option>
                <option value="mu">Mauritius</option>
                <option value="yt">Mayotte</option>
                <option value="mx">Mexico</option>
                <option value="fm">Micronesia, Federated States of</option>
                <option value="md">Moldova, Republic of</option>
                <option value="mc">Monaco</option>
                <option value="mn">Mongolia</option>
                <option value="ms">Montserrat</option>
                <option value="ma">Morocco</option>
                <option value="mz">Mozambique</option>
                <option value="mm">Myanmar</option>
                <option value="na">Namibia</option>
                <option value="nr">Nauru</option>
                <option value="np">Nepal</option>
                <option value="nl">Netherlands</option>
                <option value="an">Netherlands Antilles</option>
                <option value="nc">New Caledonia</option>
                <option value="nz">New Zealand</option>
                <option value="ni">Nicaragua</option>
                <option value="ne">Niger</option>
                <option value="ng">Nigeria</option>
                <option value="nu">Niue</option>
                <option value="nf">Norfolk Island</option>
                <option value="mp">Northern Mariana Islands</option>
                <option value="no">Norway</option>
                <option value="om">Oman</option>
                <option value="pk">Pakistan</option>
                <option value="pw">Palau</option>
                <option value="pa">Panama</option>
                <option value="pg">Papua New Guinea</option>
                <option value="py">Paraguay</option>
                <option value="pe">Peru</option>
                <option value="ph">Philippines</option>
                <option value="pn">Pitcairn</option>
                <option value="pl">Poland</option>
                <option value="pt">Portugal</option>
                <option value="pr">Puerto Rico</option>
                <option value="qa">Qatar</option>
                <option value="re">Reunion</option>
                <option value="ro">Romania</option>
                <option value="ru">Russian Federation</option>
                <option value="rw">Rwanda</option>
                <option value="kn">Saint Kitts and Nevis</option>
                <option value="lc">Saint LUCIA</option>
                <option value="vc">Saint Vincent and the Grenadines</option>
                <option value="ws">Samoa</option>
                <option value="sm">San Marino</option>
                <option value="st">Sao Tome and Principe</option>
                <option value="sa">Saudi Arabia</option>
                <option value="sn">Senegal</option>
                <option value="sc">Seychelles</option>
                <option value="sl">Sierra Leone</option>
                <option value="sg">Singapore</option>
                <option value="sk">Slovakia (Slovak Republic)</option>
                <option value="si">Slovenia</option>
                <option value="sb">Solomon Islands</option>
                <option value="so">Somalia</option>
                <option value="za">South Africa</option>
                <option value="gs">South Georgia and the South Sandwich Islands</option>
                <option value="es">Spain</option>
                <option value="lk">Sri Lanka</option>
                <option value="sh">St. Helena</option>
                <option value="pm">St. Pierre and Miquelon</option>
                <option value="sd">Sudan</option>
                <option value="sr">Suriname</option>
                <option value="sj">Svalbard and Jan Mayen Islands</option>
                <option value="sz">Swaziland</option>
                <option value="se">Sweden</option>
                <option value="ch">Switzerland</option>
                <option value="sy">Syrian Arab Republic</option>
                <option value="tw">Taiwan, Province of China</option>
                <option value="tj">Tajikistan</option>
                <option value="tz">Tanzania, United Republic of</option>
                <option value="th">Thailand</option>
                <option value="tg">Togo</option>
                <option value="tk">Tokelau</option>
                <option value="to">Tonga</option>
                <option value="tt">Trinidad and Tobago</option>
                <option value="tn">Tunisia</option>
                <option value="tr">Turkey</option>
                <option value="tm">Turkmenistan</option>
                <option value="tc">Turks and Caicos Islands</option>
                <option value="tv">Tuvalu</option>
                <option value="ug">Uganda</option>
                <option value="ua">Ukraine</option>
                <option value="ae">United Arab Emirates</option>
                <option value="gb">United Kingdom</option>
                <option selected="selected" value="us">United States</option>
                <option value="um">United States Minor Outlying Islands</option>
                <option value="uy">Uruguay</option>
                <option value="uz">Uzbekistan</option>
                <option value="vu">Vanuatu</option>
                <option value="ve">Venezuela</option>
                <option value="vn">Viet Nam</option>
                <option value="vg">Virgin Islands (British)</option>
                <option value="vi">Virgin Islands (U.S.)</option>
                <option value="wf">Wallis and Futuna Islands</option>
                <option value="eh">Western Sahara</option>
                <option value="ye">Yemen</option>
                <option value="yu">Yugoslavia</option>
                <option value="zm">Zambia</option>
                <option value="zw">Zimbabwe</option>
                </select>
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                </div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                <div id="row_4177" class="span11">
                <div id="column_5388" class="span11">
                <div class="email field_block">
                <div class="caption">Email Address <span class="required">*</span></div>
                <div class="field"><input class="text field fb-email" name="11349" required="" size="35" type="text" value="">
                <div class="field_error">&nbsp;</div>
                </div>
                </div>
                <div id="list_200083" class="list_block"><label> <span class="checkbox"> <input checked="checked" name="11350[200083]" type="checkbox" value="true"> </span> <span class="caption">Yes, I would like to receive information on new items, technical articles, discounts &amp; more.</span> </label></div>
                <em>uncheck the box to opt-out</em></div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                <div id="row_4155" class="span11">
                <div id="column_5359" class="span11">
                <div><span style="color: #ff0000;"><em><span style="font-family: georgia, palatino;">*Don\'t forget to check the correct box of the Corvette&nbsp;generation you own before hitting submit.</span></em></span></div>
                <div class="field_block">
                <div class="field"><input type="submit" value="Submit"></div>
                </div>
                <input name="11375[202413]" type="hidden" value="true"></div>
                <div style="clear: both;">&nbsp;</div>
                </div>
                </form></div>'
                            ),
                            'returns-exchanges' => array(
                                'title' => 'Returns & Exchanges',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p class="lead" style="margin-bottom: 3px;">Table of Contents:</p>
                <ol>
                <li style="list-style: decimal; margin-bottom: 5px;"><a href="/returns-exchanges#basics">Basic Policies</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="Properties" href="/returns-exchanges#receipt">Receipt of Order - Shortages</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="International" href="/returns-exchanges#damaged">Damaged Items Shipped from Zip</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="International" href="/returns-exchanges#damageddropships">Damaged Items Shipped from a Manufacturer - Dropships</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="Dropships" href="/returns-exchanges#cancel">Order Modifications and Cancellations</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="Dropships" href="/returns-exchanges#cores">Core Exchanges</a></li>
                </ol>
                <h2 id="basics">Zip Return Policies and Procedures</h2>
                <p>Zip Products includes a return form on the back of each invoice for your convenience. If you have questions or concerns regarding a return or exchange, <a title="Email Us" href="/contacts">send us and email</a> or call during regular business hours and speak to your Zip Products sales representative.</p>
                <p>Parts and accessories bought from Zip Corvette may be returned within one (1) year without a restocking fee. Return shipping is the responsibility of the customer for unwanted and undamaged parts. C.O.D. return packages are not accepted. The customer is responsible for returning parts in the same condition they were received, with proper packaging and insurance to cover the value of parts being returned.</p>
                <p>International customers, please call for authorization to return parts.</p>
                <p>If an item is ordered from Zip Products with free shipping and is returned for a credit and was not damaged or defective upon customer receipt, the shipping charges for the item will be deducted from the total refund.</p>
                <h2 id="receipt">Receipt of Order – Shortages</h2>
                <p>We ask that you please inspect all merchandise immediately upon receipt of order. Notify Zip Products of any shortages or discrepancies in your order within seven (7) days from shipment date.</p>
                <h2 id="damaged">Damaged Items from Zip Corvette</h2>
                <p>All parts shipped are inspected and insured for the amount of purchase. Delivery of the product in good condition is the responsibility of the carrier. Should your order arrive damaged or should you notice missing merchandise, immediately notify the carrier (UPS, FedEx, US Post Office etc.) AND Zip Products. Keep all shipping materials (box, packing, etc.) intact until an inspection can be made by the carrier. If you are unsure of how to handle the situation please <a title="Contact us" href="/contacts">Contact us</a> and we will assist you with the process.</p>
                <h2 id="damageddropships">Damaged Items from a Manufacturer - Dropships</h2>
                <p>In the event that an item is shipped to you direct from a manufacturer and you are unhappy with the item, it is damaged or you simply do not need the item any longer, PLEASE <a title="Contact Us" href="/contacts">Contact us</a> immediately. We will see that the proper steps are taken with the manufacturer to get your item(s) returned in a timely manner.</p>
                <h2 id="cancel">Order Cancellations and Modifications</h2>
                <h4>Order Modifications</h4>
                <p>Because we ship most orders on the same day they are placed, the window for order modification is limited. If you need to modify your order please <a title="Contact Us" href="/contacts">Contact us</a> by phone or email as soon as possible to see if modifications can be made. If your order or the specific item(s) are unable to be modified because they have already shipped, return shipping <strong>will not</strong> be covered by Zip Products though we will issue a full refund once the item(s) are received and determined to be in new condition.</p>
                <h4>Standard Item Cancellations</h4>
                <p>Because we ship most orders on the same day they are placed, the window for whole order or individual item cancellations is limited. If you need to cancel your recently placed order or specific items please <a title="Contact Us" href="/contacts">Contact us</a> by phone or email as soon as possible to see if the cancellations can be made. If your order or the specific item(s) are unable to be cancelled because they have already shipped, return shipping <strong>will not</strong> be covered by Zip Products though we will issue a full refund once the item(s) are received and determined to be in new condition.</p>
                <h4>Dropship Item Cancellations</h4>
                <p>As with items fulfilled from the Zip warehouse, dropship items are processed the same business day they are ordered. This means the window for dropship cancellations is limited therefore we ask that you <a title="Contact Us" href="/contacts">Contact us</a> by phone or email immediately if you need to cancel an order with a dropship item.</p>
                <p>In some scenarios, we are unable to cancel dropship items from an order. However, holding true to our <a title="Our Guarantee" href="/our-guarantee">100% customer satisfaction guarantee</a>, if you are unhappy with the item let us know and we can process your return in a timely manner in the form of a refund. Please <strong>NOTIFY ZIP PRODUCTS</strong> and not the manufacturer if you have a problem with your dropship item and one of our customer service/sales representatives will assist you with your problem. If a dropship item is unable to be cancelled because it has already shipped, return shipping <strong>will not</strong> be covered by Zip Products though we will issue a full refund once the item(s) are received and determined to be in new condition.</p>
                <h2 id="cores">Core Exchanges and Refunds</h2>
                <p>Customers who have purchased a product sold on an exchange basis and had a core deposit applied must return the required rebuildable core within 30 days from the date of shipment. A copy of the original invoice must accompany the return. Failure to return cores within 30 days may result in a reduction or loss of the core deposit. Return freight for cores is the responsibility of the customer.</p>'
                            ),
                            'shipping' => array(
                                'title' => 'Shipping',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<div id="contentPart" class="contentpartblock">
                <p class="lead" style="margin-bottom: 3px;">Table of Contents:</p>
                <ol>
                <li style="list-style: decimal; margin-bottom: 5px;"><a href="/shipping#domestic">Domestic Orders</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="Properties" href="/shipping#usproperties">Non-Contiguous 48 US and other Exceptions</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="International" href="/shipping#international">International Orders</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="Backorders" href="/shipping#backorders">Backorders</a></li>
                <li style="list-style: decimal; margin-bottom: 5px;"><a title="Dropships" href="/shipping#dropships">Direct Shipment from Manufacturer - Dropships</a></li>
                </ol>
                <div class="alert alert-info">All orders placed online for delivery to the 48 contiguous United States will have <em><span style="text-decoration: underline;">shipment charges calculated by shipment type, order weight and destination</span></em>. All other orders, including international orders placed online will not have shipping calculated at the time of order, but you will be charged separately. A Zip representative will contact you for approval before shipping charges are processed. Please see below for <a title="International" href="/shipping#international">International Shipping</a> and <a title="Exceptions" href="/shipping#usproperties">Non 48 United States/Exceptions</a>.</div>
                <p><strong>There is no reason to pay for "Rush" or "Same Day Processing" charges;</strong> Zip will ship your order the same day for no additional charge! Simply place your order by 3:00 PM ET Monday-Friday and all <strong>in stock</strong> items will ship from our Mechanicsville, Virginia location (zip code: 23111) the same day. A shipping confirmation with carrier and tracking information will be sent via e-mail by the end of the day that your order is shipped.</p>
                <p>Back-ordered items will ship at a later date. <a title="Backorders" href="/shipping#backorders">Click here for backorder information</a>. Products that ship direct from the manufacturer may require additional time for delivery. <a title="Dropships" href="/shipping#dropships">Click here for information about dropships</a>.</p>
                <h2 id="domestic">Domestic Orders - <small><a title="International" href="/shipping#international">Click here for International Orders</a></small></h2>
                <h4>When domestic orders are placed with Zip Products, the following shipping methods are available:</h4>
                <ul>
                <li class="first"><strong>UPS Ground:</strong> Guaranteed day of delivery to all 50 states and Puerto Rico according to above shipping map. Total freight costs calculated based on order value includes all shipping, handling and insurance costs. <strong>Additional insurance protection is NOT required</strong> when ordering from Zip.</li>
                <li><strong>UPS Next Day Air:</strong> Guaranteed <strong>next business day delivery</strong> by 10:30 a.m., 12:00 noon, or end of day, depending on destination to all 50 states and Puerto Rico, with some limitations in Alaska and Hawaii (<a title="AK and HI" href="/shipping#akhi">click here for details</a>). *Next Day Air Saturday delivery is available upon request. <a title="Contact Us" href="/contacts">Contact us</a>&nbsp;immediately after placing your order to have it setup for Saturday delivery. UPS does not deliver to all locations on Saturday therefore a special request must be made for any order to be delivered on Saturday.</li>
                <li><strong>UPS 2nd Day Air:</strong> Guaranteed delivery by the end of the second business day to All 50 states and Puerto Rico, with some limitations in Alaska and Hawaii as they may require additional transit time (<a title="AK and HI" href="/shipping#akhi">click here for details</a>).</li>
                <li><strong>UPS 3 Day Select:</strong> Guaranteed delivery by the end of the third business day to the 48 contiguous states.</li>
                <li><strong>Truck Service:</strong> Some parts in our catalog are too large to be shipped by U.P.S. These parts are noted in the catalog as "TRUCK". Truck shipments are required to be prepaid for the total parts order by check, money order or credit card. The customer is responsible for being at the indicated shipping address for acceptance of the parts.</li>
                <li class="last"><strong>U.S. Postal Service:</strong> Orders must be paid by credit card or prepaid check; C.O.D. services are not available. Charges vary due to weight and location. Orders must total less than $100 and must weight less than 6 LBS to be shipped via the U.S. Postal Service. Guaranteed 2-3 day delivery to all 50 states and Puerto Rico.</li>
                </ul>
                <h2 id="usproperties">Non-Contiguous 48 United States and other Exceptions - <small><a title="International" href="/shipping#international">Click here for International Policies</a></small></h2>
                <p>We are unable to calculate UPS shipping charges for the ship to destinations listed below because they do not fall within the contiguous 48 United States. Initial shipping charges will show $0, however we will calculate all associated shipping fees after receipt of your order based on ship method, destination and items ordered. Prior to shipping your order a representative will contact you with the total dollar amount for approval. For more information <a title="Contact Us" href="http://www.p3147871.pubip.peer1.net/contacts">Contact Us here</a></p>
                <ul>
                <li>Alaska ~ <em>more information below</em></li>
                <li>American Samoa</li>
                <li>Armed Forces ~ <em>more information below</em></li>
                <li>Federated States of Micronesia</li>
                <li>Guam ~ <em>more information below</em></li>
                <li>Hawaii ~ <em>more information below</em></li>
                <li>Marshall Islands</li>
                <li>Northern Marina Islands</li>
                <li>Puerto Rico ~ <em>more information below</em></li>
                <li>Virgin Islands ~ <em>more information below</em></li>
                </ul>
                <h4 id="akhi">Alaska, Hawaii &amp; Puerto Rico</h4>
                <p>All Puerto Rican and Hawaiian orders must be sent UPS Next Day or Second Day Air or through the Post Office. Please specify which shipping method is preferred. Alaska is divided into two UPS zones (Rural and Metropolitan). Orders to both areas may be sent UPS Second Day Air or through the Post Office. UPS Next Day Air is available only in Alaska\'s Metropolitan areas.</p>
                <h4 id="apo">A.P.O. and F.P.O.</h4>
                <p>All orders going to these addresses must be sent by the Post Office.</p>
                <h4 id="viguam">Virgin Islands and Guam</h4>
                <p>All orders going to these locations can be shipped via UPS Express or the Post Office. Please specify shipping method preferred.</p>
                <h2 id="international">International Orders - <small><a title="Properties" href="/shipping#usproperties">Click here for US Properties and other exceptions</a></small></h2>
                <p class="lead">When international orders are placed with Zip Products, the following shipping methods are available:</p>
                <ul>
                <li>UPS Expedited: Includes UPS delivery commitment of two business days to Canada, two or three business days to Mexico, three or four business days to Europe, and four or five business days to Asia and Latin America.</li>
                <li>UPS Express: Includes UPS delivery commitment of one to two business days to Canada and Mexico, second business day delivery to Europe and Latin America and two or three business days to Asia.</li>
                </ul>
                <p>We are unable to calculate shipping charges for International orders. When ordering online your initial shipping charges will show $0, however we will calculate all associated shipping fees after receipt of your order based on ship method, destination and items ordered. International orders will be shipped by the best method as determined by Zip Products. Because some orders require additional shipping fees, we will contact you before shipment for approval. If we are unable to get in touch throughout a two week period, all un-approved orders will be cancelled.</p>
                <p>All international shipping charges (except U.S. Post Office) are based on actual weight or dimensional weight according to the size of the entire shipment, whichever is greater. Payment is accepted by credit card, approved check or bank draft. C.O.D. is not available. Separate invoices for parts and shipping are created to save you on customs fees and duties. Shipping charges do not include any customs or duty fees (except for Canadian shipments - see below) and may not include brokerage fees. Quotes can be provided for total cost of order, if required. <a title="Contact Us" href="/contacts">Contact us here for a quote</a>.</p>
                <h4>Canada:</h4>
                <p>Orders must include full address with city and province. All orders are shipped UPS Ground or Air unless otherwise specified or UPS is unable to deliver to your location. To better serve our customers, Zip is recognized as being in effect an agent of the Canadian government - a status that allows us to collect all duties, taxes and brokerage fees at the time your order is processed. This arrangement means you know the exact cost to have parts delivered to your door!</p>
                <h2 id="backorders">How Zip ships your Backorders</h2>
                <p>We\'re proud that over 96% of all orders are boxed and out the door within 24 hours of receipt. However, backorders do occasionally occur. Unlike other companies, Zip does not charge you up front for backorders; you are charged only when your items ship from our warehouse. Most backorders ship within 10 business days. For longer delays, our policy is to ship orders up to 30 days old. After 30 days, we\'ll call you for authorization before shipping your order. If you wish to cancel a backorder, please notify Zip as soon as possible.</p>
                <h2 id="dropships">Direct Shipments from Manufacturer - Dropships</h2>
                <p>Some parts and accessories are unique in that we are able to ship them direct to your door from the manufacturer if we do not have them in stock. If you see an item flagged as a dropship and it is not a custom made to order part, chances are you will have that item within two weeks of placing your order. Dropships are not guaranteed to be shipped by UPS as the ship method is chosen by the manufacturer, however most manufacturers use UPS or FedEx.</p>
                <p>In some scenarios we are unable to cancel dropship items from an order, however holding true to our <a title="Our Guarantee" href="/our-guarantee">100% customer satisfaction guarantee</a>, if you are unhappy with the item let us know and we can process your return in a timely manner. <strong>Please <a title="Contact Us" href="/contacts">Contact us</a> and not the manufacturer</strong> if you have a problem with your dropship item and one of our customer service/sales representatives will assist you.</p>
                <h2 id="freeshipping">Free Shipping Promotions</h2>
                <p>Free shipping offers apply to Internet orders shipped via UPS Ground to the Contiguous United States only. All oversize shipping, truck freight, express delivery and other miscellaneous shipping charges still apply. Free shipping offers are not to be combined with any discount and apply to retail customers only - wholesale accounts not eligible. Any item returned for a refund that shipped free of charge will have the shipping amount deducted from the refund if the item is in good condition. Additional qualifications may be required for specific free shipping offers. Zip Corvette reserves the right to expire any free shipping offer without notice for any reason.</p>
                </div>'
                            ),
                            'we-install-corvette-parts' => array(
                                'title' => 'We Install Corvette Parts',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p>You\'ve known Zip is the place for Corvette Parts &amp; Corvette Accessories for your 1953 to C6 Corvettes, but now your purchase is even easier. In 2004, we opened our performance and installation facility. We now offer Zip customers a professional installation on thousand\'s of items, backed by our <a href="/Common/4dbd6de0-6989-440c-acee-0f024f35fa88.aspx?">no hassle customer satisfaction guarantee</a> and over 30 years of Corvette experience.</p>
                <p>Whether you need a trailing arm rebuild for your \'66 Corvette or want to gain a few extra horsepower from your Z06, you can count on Zip. We utilize only the best equipment for working on your Corvette. Tires up to 24" in diameter can be safely mounted without causing any damage to the wheel or tire and our precision balancing machine ensures a smooth ride while preventing problems down the road.</p>
                <p>Many of our Corvette parts are built in-house by experienced technicians. We build all of our own differentials for all generations of Corvettes, as well as trailing arm assemblies and many more parts.Our performance differentials are in SCCA Corvette race cars, as well as many satisfied customer\'s cars on the street. Use Zip Products install shop with confidence! Stop by and see a sales person or <a title="Contact Us" href="/contacts">contact us</a> for an appointment.</p>
                <p><img title="Zip Corvette Installations" src="{{media url=&quot;zip-corvette-we-install.jpg&quot;}}" alt="Zip Corvette Installations"></p>'
                            ),
                            'welcome-series-catalog-request.html' => array(
                                'title' => 'Catalog Request',
                                'layout' => '1column',
                                'heading' => '',
                                'content' =>'<p></p>'
                            ),
        );

        foreach ($pages as $identifier => $data) {
            $cmspage =  $this->pageFactory->create()->load($identifier, 'identifier');

            if ($cmspage->getId()) {
                $cmspage->setContent($data['content'])->save();
            } else {
                $cmsData = [
                    'title' => $data['title'],
                    'page_layout' => $data['layout'],
                    'identifier' => $identifier,
                    'content_heading' => $data['heading'],
                    'content' => $data['content'],
                    'is_active' => 1,
                    'stores' => [0]
                ];

                $this->pageFactory->create()->setData($cmsData)->save();
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}