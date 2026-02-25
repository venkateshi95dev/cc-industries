<?php

namespace Crimson\CokerWV\Service;

use Amasty\Finder\Model\ResourceModel\Finder;
class AssignPidAmastyFinder
{

    public function __construct(
        private readonly Finder $finder
    )
    {
    }

    public function execute(): array
    {
        $result['message'] = "Assigned related product pid data for Amasty finder entities.";
        try {
            $this->finder->updateLinks();
        }
        catch (\Exception $e){
            $result['message'] = __('Can not update links for finder data '.$e->getMessage());
        }
        return $result;
    }
}
