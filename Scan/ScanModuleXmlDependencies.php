<?php declare(strict_types=1);

namespace Yireo\ExtensionChecker\Scan;

use Yireo\ExtensionChecker\Component\Component;
use Yireo\ExtensionChecker\ComponentDetector\ModuleXmlComponentDetector;
use Yireo\ExtensionChecker\Message\MessageBucket;
use Yireo\ExtensionChecker\Message\MessageGroupLabels;

class ScanModuleXmlDependencies
{
    private ModuleXmlComponentDetector $moduleXmlComponentDetector;
    private MessageBucket $messageBucket;

    /**
     * @param ModuleXmlComponentDetector $moduleXmlComponentDetector
     * @param MessageBucket $messageBucket
     */
    public function __construct(
        ModuleXmlComponentDetector $moduleXmlComponentDetector,
        MessageBucket $messageBucket
    ) {
        $this->moduleXmlComponentDetector = $moduleXmlComponentDetector;
        $this->messageBucket = $messageBucket;
    }

    /**
     * @param string $moduleName
     * @param Component[] $components
     * @return void
     */
    public function scan(string $moduleName, array $components)
    {
        $moduleXmlComponents = $this->moduleXmlComponentDetector->getComponentsByModuleName($moduleName);
        foreach ($components as $component) {
            if ($component->getComponentType() !== 'module') {
                break;
            }

            $isComponentFoundInModuleXml = false;
            foreach ($moduleXmlComponents as $moduleXmlComponent) {
                if ($component->getComponentName() === $moduleXmlComponent->getComponentName()) {
                    $isComponentFoundInModuleXml = true;
                    break;
                }
            }

            if (!$isComponentFoundInModuleXml) {
                $message = 'Module "' . $component->getComponentName() . '" has no module.xml entry';
                $this->messageBucket->add($message, MessageGroupLabels::GROUP_MISSING_MODULEXML_DEP);
            }
        }

        foreach ($moduleXmlComponents as $moduleXmlComponent) {
            $isModuleXmlComponentFoundInDetectedComponents = false;
            foreach ($components as $component) {
                if ($component->getComponentName() === $moduleXmlComponent->getComponentName()) {
                    $isModuleXmlComponentFoundInDetectedComponents = true;
                    break;
                }
            }

            if (!$isModuleXmlComponentFoundInDetectedComponents) {
                $message = 'Module "' . $moduleXmlComponent->getComponentName() . '" is possibly not needed in module.xml';
                $this->messageBucket->add($message, MessageGroupLabels::GROUP_UNNECESSARY_MODULEXML_DEP);
            }
        }
    }
}
