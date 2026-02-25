<?php
/**
 * @namespace   Crimson
 * @module      Attrbitues
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        02/27/2019
 */
namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddProductAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory     $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productEntityId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $categorySetup->addAttribute(
            $productEntityId,
            'accessorycolor',
            [
                'label'         => 'Accessory Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Alloy',
                    'Black',
                    'Black Chrome',
                    'Black Crush',
                    'Blue',
                    'Brown',
                    'Brushed',
                    'Burlwood',
                    'Carbon Fiber',
                    'Chrome',
                    'Clear',
                    'Crystal',
                    'Dark Blue',
                    'Drivers',
                    'Electron Blue',
                    'Gloss Black',
                    'Gloss Red',
                    'Gold',
                    'Graphite',
                    'Gray',
                    'Gunmetal',
                    'Lemans Blue',
                    'Machine Silver',
                    'Matte Black',
                    'Matte Red',
                    'Mojave',
                    'Navy Blue',
                    'Orange',
                    'Pewter',
                    'Pink',
                    'Purple',
                    'Red',
                    'Rosewood',
                    'Silver',
                    'Smoke',
                    'Stainless',
                    'Tan',
                    'Teal',
                    'Torch Red',
                    'Walnut',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'accessorystyle',
            [
                'label'         => 'Accessory Style',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Auto-Dim Mirror',
                    'Black',
                    'Black with Logo',
                    'Chrome',
                    'Clear',
                    'Clear with Logo',
                    'Double Switch',
                    'Heads-Up Display',
                    'No heads-Up Display',
                    'Perforated',
                    'Ribbed',
                    'Single Switch',
                    'Smooth',
                    'Solid',
                    'Standard Mirror',
                    'Two-Tone'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'acsextfinish',
            [
                'label'         => 'ACS Exterior Finish',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Carbon Flash',
                    'Primer',
                    'Satin Black'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'apparelcolor',
            [
                'label'         => 'Apparel Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Apple Green',
                    'Ash Gray',
                    'Black',
                    'Black/Blue',
                    'Black/Gray',
                    'Black/Red',
                    'Blue',
                    'Blue/Black',
                    'Brown',
                    'Charcoal',
                    'Cherry Bomb',
                    'Chili',
                    'Creme',
                    'Daytona Sunset Orange',
                    'Denim',
                    'Fuschia',
                    'Graphite',
                    'Gray',
                    'Green',
                    'Heather Black',
                    'Heather Gray',
                    'Hot Pink',
                    'Ivory',
                    'Khaki',
                    'LeMans Blue',
                    'Machine Silver',
                    'Magnetic Red',
                    'Millenium Yellow',
                    'Millennium Yellow',
                    'Navy Blue',
                    'Orange',
                    'Pink',
                    'Purple',
                    'Raspberry',
                    'Red',
                    'Royal Blue',
                    'Sapphire Blue',
                    'Sea Blue',
                    'Silver',
                    'Stone',
                    'Sunburst Splash',
                    'Sunflower Yellow',
                    'Teal Blue',
                    'Varsity Red',
                    'Victory Red',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'apparelscriptco',
            [
                'label'         => 'Apparel Script Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Gold',
                    'Red'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'apparelsize',
            [
                'label'         => 'Apparel Size',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    '3-6 Month',
                    '12-18 Month',
                    '18-24 Month',
                    '2 Toddler',
                    '24 Month',
                    '3 Toddler',
                    '4 Toddler',
                    'X-Small',
                    'Small',
                    'Medium',
                    'Large',
                    'X-Large',
                    'XX-Large',
                    'XXX-Large',
                    'Youth Small (6-8)',
                    'Youth Medium (10-12)',
                    'Youth Large (14-16)'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'axleratio',
            [
                'label'         => 'Axle Ratio',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '3.08',
                    '3.36',
                    '3.55',
                    '3.70',
                    '4.11',
                    '4.56'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'bodystripekit',
            [
                'label'         => 'Body Stripe Kit',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '81 Blue',
                    '82 Blue',
                    'Gold',
                    'Gray',
                    'Red'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'bodystyle',
            [
                'label'         => 'Body Style',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Convertible',
                    'Coupe',
                    'Fixed Roof',
                    'Z06'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'boseradiooption',
            [
                'label'         => 'Bose Radio Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '1984 with Bose',
                    '1984-1989 with Bose',
                    '1984-1989 without Bose',
                    '1985-1989 with Bose',
                    '1985-1989 without Bose',
                    '1990-1993 with Bose',
                    '1990-1996 without Bose',
                    '1994-1996 with Bose'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'brakecolor',
            [
                'label'         => 'Brake Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Black',
                    'Blue',
                    'Gloss Black',
                    'Graphite',
                    'Matte Black',
                    'Quick-silver',
                    'Red',
                    'Silver',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'brakerotorsurfa',
            [
                'label'         => 'Brake Rotor Surface',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Cross Drilled-Slotted',
                    'J Hook'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'brands',
            [
                'label'         => 'Brands',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'AAAAAAAA',
                    'ZZZZZZZZZ',
                    '303 Conv Top',
                    '3M',
                    'A&A',
                    'AC Delco',
                    'ACI Fiberglass',
                    'Adam\'s',
                    'ADDCO',
                    'Adjure',
                    'Advanced Composite',
                    'aFe',
                    'Altec Products',
                    'American Car Craft',
                    'American Racing',
                    'Antique Auto Radio',
                    'AP Racing',
                    'ARP',
                    'ATI Performance',
                    'Attack Blue',
                    'Auto Custom Carpets',
                    'Auto Etc Neon',
                    'B&M Shifters',
                    'BBK',
                    'Bentley Publishing',
                    'BF Goodrich',
                    'BG Products',
                    'Billy Boat Exhaust',
                    'Bilstein',
                    'Block-It',
                    'Borgeson Universal',
                    'Borla',
                    'Callaway',
                    'Carbotech',
                    'CCW',
                    'Cleartastic',
                    'Cloyes',
                    'Colgan',
                    'Collectable Sign & Clock',
                    'Corbeau',
                    'Corsa',
                    'Corvette Eyewear',
                    'Corvette Rubber',
                    'Covercraft',
                    'Coverking',
                    'Dana Forrester',
                    'Design Engineering',
                    'Designer Mat',
                    'Dewitt',
                    'Diablo Sport',
                    'Driven Racing Oils',
                    'Duplicolor Paint',
                    'Eaton Detroit Spring',
                    'Edelbrock',
                    'Elite Automotive',
                    'Elite Engineering',
                    'Energy Suspension',
                    'Evercoat',
                    'Factory Reproductions',
                    'FAST',
                    'Fel Pro',
                    'Ferguson Design Covers',
                    'Fidanza',
                    'Firestone',
                    'Flowmaster',
                    'Forgeline Wheels',
                    'GM Accessory',
                    'GM Racing',
                    'Goodyear',
                    'Green Filter',
                    'Hawk',
                    'HP Tuners',
                    'Holley',
                    'Hurst Shifters',
                    'Hyperco Springs',
                    'Hypertech',
                    'Intro-Tech',
                    'Jennick Blankets',
                    'Jettco',
                    'Jim Meyer Racing',
                    'Joe Blow',
                    'K&C Harrison',
                    'Katech',
                    'Kee Auto Top',
                    'Kenwood',
                    'Kirban Performance',
                    'Klean Strip',
                    'Kooks',
                    'KYB Shocks',
                    'LG MotorSports',
                    'Lingenfelter',
                    'Lloyd Mats',
                    'MagnaFlow',
                    'Magnuson',
                    'Mantic',
                    'McGard',
                    'Metro Moulded',
                    'Meziere',
                    'Michelin',
                    'Mightymouse Solutions',
                    'Motive',
                    'MSD',
                    'Novistretch',
                    'OEM',
                    'Old Air',
                    'Palmer Engineering',
                    'Performance AFX',
                    'Pertronix',
                    'Pfadt',
                    'Phoenix Graphics',
                    'Pioneer',
                    'Power Stop',
                    'Prothane',
                    'QA1 Shocks',
                    'QuietRide Solutions',
                    'Race Ramps',
                    'RaceMesh',
                    'RaggTopp',
                    'Rain Gear',
                    'RK Sport',
                    'SandyEggo Designs',
                    'Seat Armour',
                    'Seatbelt Solutions',
                    'Silver Sport',
                    'SKF Racing',
                    'SLP Blackwing',
                    'Speed Direct',
                    'Speed Lingerie',
                    'Sprint Booster',
                    'Stainless Works',
                    'Timken',
                    'Trim Parts',
                    'US Royal',
                    'Ventmaster',
                    'Vette Net',
                    'Vintage Air',
                    'Vintage Car Audio',
                    'Wilwood',
                    'None'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'carbcertified',
            [
                'label'         => 'CARB Certified',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No',
                    'Not Required',
                    'Yes'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'carbnumber',
            [
                'label'         => 'CARB Number',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'carcovercolor',
            [
                'label'         => 'Car Cover Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Arctic White',
                    'Black',
                    'Black-Silver',
                    'Blade Silver Metallic',
                    'Blue',
                    'Blue-Black',
                    'Bright Blue',
                    'Bright Red',
                    'Charcoal Gray',
                    'Crystal Red Metallic',
                    'Dark Blue',
                    'Daytona Sunrise Orange',
                    'Galvanized',
                    'Gloss Black',
                    'Gray',
                    'Green',
                    'Hunter Green',
                    'Kalahari',
                    'Laguna Blue',
                    'Light Blue',
                    'Red',
                    'Red-Black',
                    'Red-Silver',
                    'Redline',
                    'Shark Gray',
                    'Silver Gray',
                    'Silver-Black',
                    'Silver-Red',
                    'Taupe',
                    'Torch Red',
                    'Velocity Yellow',
                    'Yellow',
                    'Yellow-Black'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'carcoverwelting',
            [
                'label'         => 'Car Cover Welting',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'With Reflective Welting',
                    'Without Reflective Welting'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'carcoveryrstyle',
            [
                'label'         => 'Car Cover Year (Body Style)',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    '05-13 Coupe',
                    '06-13 Convertible',
                    '06-13 Coupe GS Z06 ZR1',
                    '10-13 Convertible Grand',
                    '14-18 Stingray',
                    '15-18 Z06/Grand Sport',
                    '53-55',
                    '56-57',
                    '58-62',
                    '63-67',
                    '68-77',
                    '78-82',
                    '84-90 Coupe',
                    '86-90 Convertible',
                    '90-95 ZR1',
                    '91-96 Convertible',
                    '91-96 Coupe',
                    '97 Coupe',
                    '98-04 Convertible',
                    '99-04 Fixed Roof'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'convtopcolor',
            [
                'label'         => 'Convertible Top Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black',
                    'Dark Blue',
                    'Tan',
                    'White'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'corecharge',
            [
                'label'         => 'Core Charges',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '100',
                    '1000',
                    '115',
                    '1200',
                    '125',
                    '1375',
                    '150',
                    '1500',
                    '180',
                    '1800',
                    '20',
                    '200',
                    '225',
                    '240',
                    '25',
                    '250',
                    '2750',
                    '300',
                    '35',
                    '40',
                    '400',
                    '45',
                    '450',
                    '50',
                    '500',
                    '60',
                    '600',
                    '65',
                    '650',
                    '75',
                    '750',
                    '80',
                    '800',
                    '90',
                    '900'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'decalcolor',
            [
                'label'         => 'Decal Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Arctic White (10U)',
                    'Atomic Orange (83U)',
                    'Black',
                    'Carbon Fiber',
                    'Crystal Red (89U)',
                    'Daytona Sunset Orange (71U)',
                    'Gold',
                    'Gold Metallic',
                    'Jetstream Blue (85U)',
                    'Light Oak',
                    'Machine Silver (67U)',
                    'Red',
                    'Silver',
                    'Silver Metallic',
                    'Velocity Yellow (45U)',
                    'Victory Red (74U)',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'decalsize',
            [
                'label'         => 'Decal Size',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Medium',
                    'Small'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'difficultylevel',
            [
                'label'         => 'Difficulty Level',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                    'None'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'emblemcolor',
            [
                'label'         => 'Emblem Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Beige',
                    'Black',
                    'Blue',
                    'Carbon Fiber',
                    'Charcoal',
                    'Chrome',
                    'Gold',
                    'Gray',
                    'Mirror Chrome',
                    'Orange',
                    'Red',
                    'Silver',
                    'White',
                    'Z06'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'emblemoption',
            [
                'label'         => 'Emblem Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'C1 Corvette',
                    'C2 Corvette',
                    'C3 Corvette',
                    'C4 Corvette',
                    'C5 Corvette',
                    'C5 Z06 Corvette',
                    'C6 Corvette',
                    'C6 Z06 Corvette',
                    'C7 Corvette',
                    'With Embroidered Emblem',
                    'Without Embroidered Emblem',
                    'Z06',
                    'Z06 405hp'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'fiberglasscolor',
            [
                'label'         => 'Fiberglass Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Black',
                    'Gray',
                    'White'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'floormatemblem',
            [
                'label'         => 'Floor Mat Emblem',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Z06',
                    'Z06 405hp'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'flrmtembcol',
            [
                'label'         => 'Floor Mat Emblem Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Black',
                    'Blue',
                    'Gold',
                    'Red',
                    'Red with Black',
                    'Red with Silver',
                    'Silver',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'forgelinectrwhl',
            [
                'label'         => 'Forgeline Center Wheel Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Gloss Black',
                    'Gunmetal',
                    'Matte Black',
                    'Optional',
                    'Satin Black',
                    'Silver',
                    'Titanium'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'forgelinewhllip',
            [
                'label'         => 'Forgeline Wheel Lip Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Clear',
                    'Fly Yellow',
                    'Gloss Black',
                    'Gunmetal',
                    'Matte Black',
                    'Satin Black',
                    'Silver',
                    'Transparent Red',
                    'Victory Red'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'freeshipping',
            [
                'label'         => 'Free Shipping',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No',
                    'Yes'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'frmszsunglasses',
            [
                'label'         => 'Frame Size (Sunglasses)',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Large (55-17-135)',
                    'Medium (53-16-135)'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'glass',
            [
                'label'         => 'Glass',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Clear',
                    'Custom Grey',
                    'Green Tint'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'gmpartnumber',
            [
                'label'         => 'GM Part Number',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'horsepower',
            [
                'label'         => 'Horsepower #',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '0',
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                    '6',
                    '7',
                    '8',
                    '9'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'hscode',
            [
                'label'         => 'HS Code',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'illumination',
            [
                'label'         => 'Illumination',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Amber',
                    'Blue',
                    'Green',
                    'Red',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'intcoloptgen',
            [
                'label'         => 'Interior Color Options (Generic)',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Beige',
                    'Black',
                    'Blue',
                    'Bright Blue',
                    'Bronze',
                    'Brown',
                    'Caramel',
                    'Cashmere',
                    'Charcoal',
                    'Cream',
                    'Dark Blue',
                    'Dark Red',
                    'Ebony',
                    'Firethorn Red',
                    'Flame Red',
                    'Graphite',
                    'Gray',
                    'Green',
                    'Lapis Blue',
                    'Light Gray',
                    'Light Oak',
                    'Light Saddle',
                    'Maroon',
                    'Medium Blue',
                    'Medium Gray',
                    'Midnight Blue',
                    'Oyster',
                    'Red',
                    'Ruby Red',
                    'Saddle',
                    'Silver',
                    'Smoke',
                    'Tan',
                    'Taupe',
                    'Torch Red',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'interiorcolor',
            [
                'label'         => 'Interior Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    '00-04 Torch Red',
                    '01-04 Z06',
                    '03-04 Shale',
                    '05 Steel Gray',
                    '05-13 Cashmere',
                    '05-13 Cobalt Red',
                    '05-13 Ebony',
                    '05-13 Varsity Red',
                    '06-13 Light Titanium',
                    '09-13 Dark Titanium',
                    '14-16 Brownstone',
                    '14-16 Jet Black',
                    '53-55 Beige',
                    '53-55 Red',
                    '55 Harvest Gold',
                    '55 Ivory',
                    '56-57 Beige',
                    '56-57 Red',
                    '58 Charcoal',
                    '58 Red',
                    '58-60 Frost Blue',
                    '59-60 Turquoise',
                    '59-64 Red',
                    '59-96 (59-04 Black)',
                    '61 Blue',
                    '61-62 Fawn Beige',
                    '61-62 White (Headliner)',
                    '63-64 Dark Blue',
                    '63-64 Saddle',
                    '64-66 Silver',
                    '64-67 White',
                    '65 Maroon',
                    '65-66 Saddle',
                    '65-67 Bright Blue',
                    '65-67 Green',
                    '65-67 Red',
                    '66 Dark Blue',
                    '67 Saddle',
                    '67 Teal Blue',
                    '68 Dark Blue',
                    '68 Dark Orange',
                    '68 Tobacco',
                    '68-69 Gunmetal',
                    '68-69 Saddle',
                    '68-70 Bright Blue',
                    '68-72 Red',
                    '69-79-80 Green',
                    '70 Green',
                    '70 Light Saddle',
                    '70-72 Dark Saddle',
                    '71 Green',
                    '71-72 Royal Blue',
                    '73 Dark Saddle',
                    '73-75 Dark Blue',
                    '73-75 Medium Saddle',
                    '73-75 Oxblood',
                    '74-75 Neutral',
                    '74-75 Silver',
                    '76 Blue Green',
                    '76 Buckskin (Dark)',
                    '76 Firethorn Red',
                    '76 White-Black',
                    '76 White-Brown',
                    '76 White-Firethorn Red',
                    '76 White-Smoke',
                    '76 White-Smoke (Dark)',
                    '76-77 Buckskin',
                    '76-77 Buckskin Light',
                    '76-77 Smoke',
                    '76-77 Smoke (Dark)',
                    '76-77 White',
                    '76-78 Dark Brown',
                    '77 Dark Blue',
                    '77-81 Red',
                    '78 Dark Blue',
                    '78 Doeskin (Light)',
                    '78 Oyster',
                    '78 Pace Car Silver',
                    '78 Saffron',
                    '78-80 Doeskin',
                    '78-80 Doeskin (Dark)',
                    '78-81 Dark Blue',
                    '79-80 Oyster',
                    '79-81 Dark Blue',
                    '80 Claret',
                    '81 Cinnabar',
                    '81 Silver',
                    '81-82 Camel',
                    '81-82 Camel (Light)',
                    '82 Charcoal',
                    '82 Dark Blue',
                    '82 Gray',
                    '82 Red',
                    '82 Silver Beige (Collector)',
                    '82 Silver Green',
                    '84-85 Carmine Red',
                    '84-85 Medium Blue',
                    '84-87 Bronze',
                    '84-87 Graphite',
                    '84-87 Medium Gray',
                    '84-87 Saddle',
                    '86-89 Blue',
                    '86-92 Flame Red',
                    '88 White',
                    '88-89 Gray',
                    '88-91 Saddle',
                    '90-91 Blue',
                    '90-91 Gray',
                    '92-93 White',
                    '92-96 Beige',
                    '92-96 Gray',
                    '93 Ruby Red (Anniversary)',
                    '93-96 Red',
                    '97-04 Black',
                    '97-04 Black w-Red Insert',
                    '97-04 Light Gray',
                    '97-99 Firethorn Red',
                    '98-04 Light Oak',
                    'Natural',
                    'Parchment'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'iskit',
            [
                'label'         => 'Is Kit',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'leadtimedays',
            [
                'label'         => 'Lead Time Days',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'licplateoption',
            [
                'label'         => 'License Plate Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'With Front License Plate',
                    'Without Front License Plate'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'manufacturer',
            [
                'label'         => 'Manufacturer',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'manufacturersku',
            [
                'label'         => 'Manufacturer SKU',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'manufacturerskudisplay',
            [
                'label'         => 'Manufacturer SKU - For Display',
                'input'         => 'text',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'marketplaceitem',
            [
                'label'         => 'Marketplace Item',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'material',
            [
                'label'         => 'Material',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'OEM Steel',
                    'Stainless'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'oilsystemoption',
            [
                'label'         => 'Oil System Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Dry Sump Oil',
                    'Oil Fill'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'paintcolor',
            [
                'label'         => 'Paint Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Admiral Blue (GTR)',
                    'Anniversary Red (94U)',
                    'Arctic White (10U)',
                    'Atomic Orange (83U)',
                    'Black (41U)',
                    'Black Rose Metallic (73U)',
                    'Black Rose Metallic (GGA)',
                    'Blade Silver Metallic (17U)',
                    'Bowling Green (91U)',
                    'Bright Aqua Metallic (43U)',
                    'Bright Red (72)',
                    'Bright Red (81U)',
                    'Bright Silver Metallic (16U)',
                    'Bright White (40U)',
                    'Carbon Fiber',
                    'Carbon Flash Metallic (58U)',
                    'Carlisle Blue (GLF)',
                    'Cashmere',
                    'Charcoal Metallic (96)',
                    'Chrome',
                    'Cobalt Red (Interior Color)',
                    'Competition Yellow (53U)',
                    'Copper Metallic (66)',
                    'Corvette Racing Yellow (386A)',
                    'Crystal Red Metallic (89U)',
                    'Cyber Gray Metallic (57U)',
                    'Dark Blue Metallic (28U)',
                    'Dark Bronze Metallic (66U)',
                    'Dark Purple Metallic (05U)',
                    'Dark Red Metallic (68)',
                    'Dark Red Metallic (74)',
                    'Daytona Sunrise Orange (G1H)',
                    'Daytona Sunset Orange (71U)',
                    'Electron Blue (21U)',
                    'Fairway Green (87U)',
                    'Flat Black',
                    'Gloss Black',
                    'Gold Metallic (53U)',
                    'Gray Metallic',
                    'Inferno Orange (28U)',
                    'Jetstream Blue',
                    'Laguna Blue (G7H)',
                    'Lemans Blue (19U)',
                    'Light Blue Metallic (20)',
                    'Light Bronze Metallic (63)',
                    'Light Carmine Red (53U)',
                    'Light Pewter Metallic (11U)',
                    'Lime Rock Green (G7J)',
                    'Long Beach Red Metallic (405Y)',
                    'Machine Silver (67U)',
                    'Magnetic Red (86U)',
                    'Magnetic Red II (86U)',
                    'Matte Black',
                    'Medium Blue Metallic (23U)',
                    'Medium Brown Metallic (69)',
                    'Medium Gray Metallic (18)',
                    'Medium Purple Pearl (95U)',
                    'Medium Spiral Gray (88U)',
                    'Millennium Yellow (79U)',
                    'Monterey Red (80U)',
                    'Nassau Blue Metallic (23U)',
                    'Navy Blue Metallic (28U)',
                    'Night Race Blue Metallic (GXH)',
                    'Pace Car Purple (21U)',
                    'Polo Green (90-91/94-96)',
                    'Polo Green II (45U) (92-93)',
                    'Precision Red (27U)',
                    'Quasar Blue (80)',
                    'Quicksilver (12U)',
                    'Ruby Red',
                    'Sebring Silver (13U)',
                    'Shark Gray (G1B)',
                    'Silver Beige (59)',
                    'Silver Metallic (13U)',
                    'Speedway White (40U)',
                    'Steel Blue (25U)',
                    'Sterling Blue (GGB)',
                    'Supersonic Blue (GLB)',
                    'Torch Red (70U)',
                    'Turquoise Metallic (42U)',
                    'Velocity Yellow (45U)',
                    'Victory Red (74U)',
                    'Watkins Glen Gray (G7Q)',
                    'Yellow (35U)'
                ]]
            ]
        );
        
        $categorySetup->addAttribute(
            $productEntityId,
            'percarusage',
            [
                'label'         => 'Per Car Usage',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'pleatsize',
            [
                'label'         => 'Pleat Size',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '2 inch',
                    '4 inch'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'powertopoption',
            [
                'label'         => 'Power Top Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Non Power Top',
                    'Power Top'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'powerwindowopti',
            [
                'label'         => 'Power Window Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Manual Windows',
                    'Power Windows'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'pricefactor',
            [
                'label'         => 'Price Factor',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                    '6',
                    'ND',
                    'NDR'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'productgroupcode',
            [
                'label'         => 'Product Group Code',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'productvideosgeneral',
            [
                'label'         => 'Generic Product Videos',
                'input'         => 'select',
                'type'          => 'textarea',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'promocopy',
            [
                'label'         => 'Promo Copy (Additional Sales Text)',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'promocopydate',
            [
                'label'         => 'Promo Copy Expire Date',
                'input'         => 'date',
                'type'          => 'static',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'prop65',
            [
                'label'         => 'Prop 65',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '1',
                    '10',
                    '11',
                    '12',
                    '2',
                    '3',
                    '4',
                    '5',
                    '6',
                    '7',
                    '8',
                    '9',
                    'No'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'racemeshfinisho',
            [
                'label'         => 'RaceMesh Finish Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black Chrome',
                    'Black Powder',
                    'Electro Polished',
                    'Silver Leopard',
                    'Stainless'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'radiatorfinish',
            [
                'label'         => 'Radiator Finish',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black Ice Coat',
                    'Natural',
                    'Polished',
                    'Polished with Chrome Fan'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'radiofaceoption',
            [
                'label'         => 'Radio Face Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black',
                    'Chrome'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'roofpaneloption',
            [
                'label'         => 'Roof Panel Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '84-86E',
                    '86L-88',
                    '89-96',
                    'Blue',
                    'Bronze'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'rubbertitematco',
            [
                'label'         => 'RubberTite Mat Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black',
                    'Clear',
                    'Gray',
                    'Tan'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'scriptcolor',
            [
                'label'         => 'Script Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Beige',
                    'Black',
                    'Blue',
                    'Bright Blue',
                    'Chrome',
                    'Orange',
                    'Red',
                    'Silver',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'scriptoption',
            [
                'label'         => 'Script Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Corvette',
                    'LS1',
                    'LS6'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'selectiverideco',
            [
                'label'         => 'Selective Ride Control',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Non Selective Ride',
                    'Selective Ride'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'shftknbscrptcol',
            [
                'label'         => 'Shift Knob Script Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Black',
                    'Red',
                    'Silver',
                    'White'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'shiftknobfinish',
            [
                'label'         => 'Shift Knob Finish',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Black',
                    'Blue',
                    'Carbon Fiber',
                    'Clear',
                    'Polished',
                    'Rosewood',
                    'White'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'shiftpatstrpcol',
            [
                'label'         => 'Shift Pattern-Stripe Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black',
                    'Gray',
                    'Red',
                    'White'
                ]]
            ]
        );
        
        $categorySetup->addAttribute(
            $productEntityId,
            'shippingcharge',
            [
                'label'         => 'Oversize Shipping / Crate Fee',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '5.00',
                    '10.00',
                    '10.50',
                    '10.85',
                    '15.00',
                    '15.85',
                    '20.00',
                    '20.85',
                    '25.85',
                    '30.00',
                    '30.85',
                    '35.00',
                    '40.85',
                    '45.00',
                    '50.00',
                    '60.00',
                    '65.00',
                    '67.50',
                    '70.00',
                    '75.00',
                    '80.00',
                    '85.00',
                    '120.00',
                    '145.00',
                    '0.00'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'side',
            [
                'label'         => 'Side',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Center',
                    'Left',
                    'Right'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'sidefrontrear',
            [
                'label'         => 'Side/Front or Rear',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Left Front',
                    'Left Rear',
                    'Right Front',
                    'Right Rear'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'spdlingcamopt',
            [
                'label'         => 'Camera Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No Front Camera',
                    'With Camera'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'spdlingcolopt',
            [
                'label'         => 'Speed Lingerie Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Admiral Blue (28U)',
                    'Admiral Blue (GTR)',
                    'Admiral Blue-White (Grand Sport)',
                    'Anniversary Red (94U)',
                    'Arctic White (10U)',
                    'Atomic Orange (83U)',
                    'Black (41U)',
                    'Black Rose (73U)',
                    'Black Rose (GGA)',
                    'Blade Silver (17U)',
                    'Bowling Green (91)',
                    'Bright Aqua Metallic (43U)',
                    'Bright Red (81U)',
                    'Bright Silver Metallic (16U)',
                    'Bright White (40U)',
                    'Carbon Fiber',
                    'Carbon Flash Black (58U)',
                    'Carlisle Blue (GLF)',
                    'Cashmere (Interior Color)',
                    'Cobalt Red (Interior Color)',
                    'Competition Yellow (53U)',
                    'Corvette Racing Yellow (386A)',
                    'Crystal Red (89U)',
                    'Cyber Gray (57U)',
                    'Dark Blue Metallic (28U)',
                    'Dark Bronze Metallic (66U)',
                    'Dark Purple Metallic (05U)',
                    'Dark Purple-White (05-10U)',
                    'Dark Red Metallic (68)',
                    'Daytona Sunrise Orange (G1H)',
                    'Daytona Sunset Orange (71U)',
                    'Ebony (Interior Color)',
                    'Electron Blue (21U)',
                    'Fairway Green (87U)',
                    'Inferno Orange (28U)',
                    'Jetstream Blue (85U)',
                    'Laguna Blue (G7H)',
                    'LeMans Blue (19U)',
                    'Light Carmine Red (53U)',
                    'Light Pewter Metallic (11U)',
                    'Lime Rock Green (G7J)',
                    'Long Beach Red Metallic (405Y)',
                    'Machine Silver (67U)',
                    'Magnetic Red (86U)',
                    'Magnetic Red II (86U)',
                    'Matte Black',
                    'Medium Blue Metallic (23U)',
                    'Medium Purple Pearl (95U)',
                    'Medium Spiral Gray (88U)',
                    'Millennium Yellow (79U)',
                    'Monterey Red (80U)',
                    'Nassau Blue Metallic (23U)',
                    'Navy Blue Metallic (28U)',
                    'Night Race Blue Metallic (GXH)',
                    'Pace Car Purple (21U)',
                    'Pace Car Yellow',
                    'Polo Green (91-45U) (90-91-94-96)',
                    'Polo Green II (45U) (92-93)',
                    'Precision Red (27U)',
                    'Quasar Blue (80)',
                    'Quicksilver (12U)',
                    'Ruby Red (68U)',
                    'Sebring Silver (13U)',
                    'Shark Gray (G1B)',
                    'Silver Beige (59)',
                    'Silver Metallic (13U)',
                    'Speedway White (40U)',
                    'Steel Blue (25U)',
                    'Sterling Blue (GGB)',
                    'Supersonic Blue (GLB)',
                    'Titanium Gray (Interior Color)',
                    'Torch Red (70U)',
                    'Turquoise Metallic (42U)',
                    'Velocity Yellow (45U)',
                    'Victory Red (74U)',
                    'Victory Red (74U)',
                    'Watkins Glen Gray (G7Q)',
                    'Yellow (35U)'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'stainlessfinish',
            [
                'label'         => 'Stainless Finish',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => true,
                'option'        => ['values' => [
                    'Brushed',
                    'Polished'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'suspensionoptio',
            [
                'label'         => 'Suspension Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Big Block',
                    'Competition',
                    'Drop Sway Bar',
                    'Hard Springs',
                    'Medium Springs',
                    'Small Block',
                    'Soft Springs',
                    'Standard',
                    'Straight Sway Bar'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'tansmissionopti',
            [
                'label'         => 'Transmission Option',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '.50 1st Gear',
                    '.63 1st Gear',
                    '.64 1st Gear',
                    '.82 1st Gear',
                    'Automatic Transmission',
                    'Manual Transmission'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'tire',
            [
                'label'         => 'Tire',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Michelin PS2',
                    'Pirelli P-Zero'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'truckship',
            [
                'label'         => 'Truck Ship',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No',
                    'Yes'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'ultimatflrmtcol',
            [
                'label'         => 'Ultimat Floor Mat Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Almond',
                    'Beige',
                    'Black',
                    'Blue',
                    'Brownstone',
                    'Cashmere',
                    'Coffee',
                    'Dark Blue',
                    'Dark Burgundy',
                    'Dark Grey',
                    'Ebony',
                    'Firethorn Red',
                    'Flame Red',
                    'Graphite',
                    'Grey',
                    'Jet Black',
                    'Light Gray',
                    'Light Oak',
                    'Medium Saddle',
                    'Red',
                    'Ruby Red (Anniversary)',
                    'Saddle',
                    'Shale',
                    'Titanium Gray',
                    'Torch Red',
                    'Varsity Red'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'upc',
            [
                'label'         => 'UPC',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'usamade',
            [
                'label'         => 'USA Made',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No',
                    'None',
                    'Null',
                    'Yes'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'vatsohmrating',
            [
                'label'         => 'VATS Ohm Rating',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '1-402 Ohms',
                    '10-3740 Ohms',
                    '11-4750 Ohms',
                    '12-6040 Ohms',
                    '13-7500 Ohms',
                    '14-9530 Ohms',
                    '15-11800 Ohms',
                    '2-523 Ohms',
                    '3-681 Ohms',
                    '4-887 Ohms',
                    '5-1130 Ohms',
                    '6-1470 Ohms',
                    '7-1870 Ohms',
                    '8-2370 Ohms',
                    '9-3010 Ohms'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'velourtexflrmtc',
            [
                'label'         => 'Velourtex Floor Mat Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Black',
                    'Ebony (Graphite)',
                    'Grey',
                    'Light Oak',
                    'Light Tan (Cashmere-Beige)',
                    'Shale',
                    'Torch Red'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'webupc',
            [
                'label'         => 'Web UPC',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 0,
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'user_defined'  => true,
                'filterable'    => false
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'wheelstripecolo',
            [
                'label'         => 'Wheel Stripe Color',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'Blue',
                    'Orange',
                    'Red',
                    'Silver',
                    'White',
                    'Yellow'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'wheelstyle',
            [
                'label'         => 'Wheel Style',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'C5 2000',
                    'C5 Z06',
                    'C6 2008',
                    'C6 2010',
                    'C6 2011',
                    'C6 Z06',
                    'Grand Sport',
                    'Z51',
                    'ZR1',
                    '60th Anniversary'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'willdropship',
            [
                'label'         => 'Vendor Will Dropship',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    'No',
                    'Yes'
                ]]
            ]
        );

        $categorySetup->addAttribute(
            $productEntityId,
            'year',
            [
                'label'         => 'Year',
                'input'         => 'select',
                'type'          => 'text',
                'visible'       => true,
                'required'      => false,
                'default'       => 'None',
                'group'         => 'General',
                'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend'       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'user_defined'  => true,
                'filterable'    => false,
                'option'        => ['values' => [
                    '1953',
                    '1953-1955',
                    '1954',
                    '1955',
                    '1956',
                    '1956-1957',
                    '1957',
                    '1958',
                    '1958-1959',
                    '1958-1960',
                    '1959',
                    '1960',
                    '1961',
                    '1961-1962',
                    '1962',
                    '1963',
                    '1963-1964',
                    '1964',
                    '1965',
                    '1966',
                    '1967',
                    '1968',
                    '1968-1969',
                    '1968-1971',
                    '1969',
                    '1970',
                    '1970-1972',
                    '1971',
                    '1972',
                    '1972-1976',
                    '1973',
                    '1973-1974',
                    '1974',
                    '1975',
                    '1975-1977',
                    '1976',
                    '1977',
                    '1978',
                    '1978 Pace Car',
                    '1978-1979',
                    '1978-1980',
                    '1979',
                    '1980',
                    '1980-1982',
                    '1981',
                    '1981-1982',
                    '1982',
                    '1984',
                    '1984-1986',
                    '1984-1988',
                    '1984-1989',
                    '1985',
                    '1986',
                    '1987',
                    '1987-1989',
                    '1988',
                    '1989',
                    '1989-1990',
                    '1989-1993',
                    '1990',
                    '1990-1991',
                    '1990-1993',
                    '1991',
                    '1991-1993',
                    '1992',
                    '1992-1993',
                    '1993',
                    '1994',
                    '1994-1996',
                    '1995',
                    '1996',
                    '1997',
                    '1998',
                    '1999',
                    '2000',
                    '2001',
                    '2002',
                    '2003',
                    '2004',
                    '2005',
                    '2005-2008',
                    '2006',
                    '2006-2013',
                    '2007',
                    '2008',
                    '2009',
                    '2009-2013',
                    '2010',
                    '2011',
                    '2012',
                    '2013',
                    '2014',
                    '2015',
                    '2016',
                    '2017'
                ]]
            ]
        );

    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [
            \Crimson\Attributes\Setup\Patch\Data\AddProductUpdateAttribute::class
        ];
    }
}