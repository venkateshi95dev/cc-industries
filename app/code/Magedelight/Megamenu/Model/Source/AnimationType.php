<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AnimationType implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => __('Attention Seekers'), 'value' => [
                [
                    'label' => 'bounce',
                    'value' => 'bounce'
                ],
                [
                    'label' => 'flash',
                    'value' => 'flash'
                ],
                [
                    'label' => 'pulse',
                    'value' => 'pulse'
                ],
                [
                    'label' => 'rubber-band',
                    'value' => 'rubber-band'
                ],
                [
                    'label' => 'shake',
                    'value' => 'shake'
                ],
                [
                    'label' => 'swing',
                    'value' => 'swing'
                ],
                [
                    'label' => 'tada',
                    'value' => 'tada'
                ],
                [
                    'label' => 'wobble',
                    'value' => 'wobble'
                ],
                [
                    'label' => 'jello',
                    'value' => 'jello'
                ],
            ],
        ];
        $options[] = ['label' => __('Bouncing Entrances'), 'value' => [
                [
                    'label' => 'bounce-in',
                    'value' => 'bounce-in'
                ],
                [
                    'label' => 'bounce-in-down',
                    'value' => 'bounce-in-down'
                ],
                [
                    'label' => 'bounce-in-left',
                    'value' => 'bounce-in-left'
                ],
                [
                    'label' => 'bounce-in-right',
                    'value' => 'bounce-in-right'
                ],
                [
                    'label' => 'bounce-in-up',
                    'value' => 'bounce-in-up'
                ],
            ],
        ];
        $options[] = ['label' => __('Bouncing Exits'), 'value' => [
                [
                    'label' => 'bounce-out',
                    'value' => 'bounce-out'
                ],
                [
                    'label' => 'bounce-out-down',
                    'value' => 'bounce-out-down'
                ],
                [
                    'label' => 'bounce-out-left',
                    'value' => 'bounce-out-left'
                ],
                [
                    'label' => 'bounce-out-right',
                    'value' => 'bounce-out-right'
                ],
                [
                    'label' => 'bounce-out-up',
                    'value' => 'bounce-out-up'
                ],
            ],
        ];
        $options[] = ['label' => __('Fading Entrances'), 'value' => [
                [
                    'label' => 'fade-in',
                    'value' => 'fade-in'
                ],
                [
                    'label' => '.fade-in-down',
                    'value' => '.fade-in-down'
                ],
                [
                    'label' => '.fade-in-down-big',
                    'value' => '.fade-in-down-big'
                ],
                [
                    'label' => 'fade-in-left',
                    'value' => 'fade-in-left'
                ],
                [
                    'label' => 'fade-in-left-big',
                    'value' => 'fade-in-left-big'
                ],
                [
                    'label' => 'fade-in-right',
                    'value' => 'fade-in-right'
                ],
                [
                    'label' => 'fade-in-right-big',
                    'value' => 'fade-in-right-big'
                ],
                [
                    'label' => 'fade-in-up',
                    'value' => 'fade-in-up'
                ],
                [
                    'label' => 'fade-in-up-big',
                    'value' => 'fade-in-up-big'
                ],
            ],
        ];
        $options[] = ['label' => __('Fading Exits'), 'value' => [
                [
                    'label' => 'fade-out',
                    'value' => 'fade-out'
                ],
                [
                    'label' => 'fade-out-down',
                    'value' => 'fade-out-down'
                ],
                [
                    'label' => 'fade-out-down-big',
                    'value' => 'fade-out-down-big'
                ],
                [
                    'label' => 'fade-out-left',
                    'value' => 'fade-out-left'
                ],
                [
                    'label' => 'fade-out-left-big',
                    'value' => 'fade-out-left-big'
                ],
                [
                    'label' => 'fade-out-right',
                    'value' => 'fade-out-right'
                ],
                [
                    'label' => 'fade-out-right-big',
                    'value' => 'fade-out-right-big'
                ],
                [
                    'label' => 'fade-out-up',
                    'value' => 'fade-out-up'
                ],
                [
                    'label' => 'fade-out-up-big',
                    'value' => 'fade-out-up-big'
                ],
            ],
        ];
        $options[] = ['label' => __('Flippers'), 'value' => [
                [
                    'label' => 'flip',
                    'value' => 'flip'
                ],
                [
                    'label' => 'flip-in-x',
                    'value' => 'flip-in-x'
                ],
                [
                    'label' => 'flip-in-y',
                    'value' => 'flip-in-y'
                ],
                [
                    'label' => 'flip-out-x',
                    'value' => 'flip-out-x'
                ],
                [
                    'label' => 'flip-out-y',
                    'value' => 'flip-out-y'
                ]
            ],
        ];
        $options[] = ['label' => __('Lightspeed'), 'value' => [
                [
                    'label' => 'light-speed-in',
                    'value' => 'light-speed-in'
                ],
                [
                    'label' => 'light-speed-out',
                    'value' => 'light-speed-out'
                ]
            ],
        ];
        $options[] = ['label' => __('Rotating Entrances'), 'value' => [
                [
                    'label' => 'rotate-in',
                    'value' => 'rotate-in'
                ],
                [
                    'label' => 'rotate-in-down-left',
                    'value' => 'rotate-in-down-left'
                ],
                [
                    'label' => 'rotate-in-down-right',
                    'value' => 'rotate-in-down-right'
                ],
                [
                    'label' => 'rotate-in-up-left',
                    'value' => 'rotate-in-up-left'
                ],
                [
                    'label' => 'rotate-in-up-right',
                    'value' => 'rotate-in-up-right'
                ]
            ],
        ];
        $options[] = ['label' => __('Rotating Exits'), 'value' => [
                [
                    'label' => 'rotate-out',
                    'value' => 'rotate-out'
                ],
                [
                    'label' => 'rotateout-down-left',
                    'value' => 'rotateout-down-left'
                ],
                [
                    'label' => 'rotateout-down-right',
                    'value' => 'rotateout-down-right'
                ],
                [
                    'label' => 'rotate-out-up-left',
                    'value' => 'rotate-out-up-left'
                ],
                [
                    'label' => 'rotate-out-up-right',
                    'value' => 'rotate-out-up-right'
                ]
            ],
        ];
        $options[] = ['label' => __('Sliding Entrances'), 'value' => [
                [
                    'label' => 'slide-in-up',
                    'value' => 'slide-in-up'
                ],
                [
                    'label' => 'slide-in-down',
                    'value' => 'slide-in-down'
                ],
                [
                    'label' => 'slide-in-left',
                    'value' => 'slide-in-left'
                ],
                [
                    'label' => 'slide-in-right',
                    'value' => 'slide-in-right'
                ]
            ],
        ];
        $options[] = ['label' => __('Sliding Exits'), 'value' => [
                [
                    'label' => 'slide-out-up',
                    'value' => 'slide-out-up'
                ],
                [
                    'label' => 'slideout-down',
                    'value' => 'slideout-down'
                ],
                [
                    'label' => 'slide-out-left',
                    'value' => 'slide-out-left'
                ],
                [
                    'label' => 'slideout-right',
                    'value' => 'slideout-right'
                ]
            ]
        ];
        $options[] = ['label' => __('Zoom Entrances'), 'value' => [
                [
                    'label' => 'zoom-in',
                    'value' => 'zoom-in'
                ],
                [
                    'label' => 'zoom-in-down',
                    'value' => 'zoom-in-down'
                ],
                [
                    'label' => 'zoom-in-left',
                    'value' => 'zoom-in-left'
                ],
                [
                    'label' => 'zoom-in-right',
                    'value' => 'zoom-in-right'
                ],
                [
                    'label' => 'zoom-in-up',
                    'value' => 'zoom-in-up'
                ]
            ]
        ];
        $options[] = ['label' => __('Zoom Exits'), 'value' => [
                [
                    'label' => 'zoom-out',
                    'value' => 'zoom-out'
                ],
                [
                    'label' => 'zoomout-down',
                    'value' => 'zoomout-down'
                ],
                [
                    'label' => 'zoom-out-left',
                    'value' => 'zoom-out-left'
                ],
                [
                    'label' => 'zoomout-right',
                    'value' => 'zoomout-right'
                ],
                [
                    'label' => 'zoom-out-up',
                    'value' => 'zoom-out-up'
                ]
            ]
        ];
        $options[] = ['label' => __('Specials'), 'value' => [
                [
                    'label' => 'hinge',
                    'value' => 'hinge'
                ],
                [
                    'label' => 'roll-in',
                    'value' => 'roll-in'
                ],
                [
                    'label' => 'roll-out',
                    'value' => 'roll-out'
                ]
            ]
        ];
        return $options;
    }
}
