<?php

namespace SchmitzIT\CustomTabs\Template\Helper;

use SchmitzIT\CustomTabs\Entity\CustomTab;
use SchmitzIT\CustomTabs\Entity\CustomTabContent;

class ResourceTabHelper
{
    /**
     * Template-modification callback.
     *
     * XenForo calls this with:
     *   string  &$html       – the fully rendered template HTML (modify in place)
     *   array    $vars       – template variables
     *   string   $template   – template name ("xfrm_resource_view")
     *   \XF\Template\Templater $templater
     *
     * We look for the closing </xf:tabs> equivalent in the rendered output – in
     * practice XF has already compiled the template to HTML at this point, so we
     * search for the last </li> inside the tab-nav and the last tab pane closing
     * div, then inject our own nav item + pane.
     */
    public static function injectTabs(
        string &$html,
        array $vars,
        string $template,
        \XF\Template\Templater $templater
    ): void {
        /** @var CustomTab[]        $tabs       */
        /** @var CustomTabContent[] $contentMap */
        $tabs       = $vars['schmitzit_custom_tabs']        ?? [];
        $contentMap = $vars['schmitzit_custom_tab_content'] ?? [];
        $resource   = $vars['resource']                     ?? null;

        if (empty($tabs) || !$resource) {
            return;
        }

        $navItems  = '';
        $tabPanes  = '';

        foreach ($tabs as $tabId => $tab) {
            /** @var CustomTabContent|null $content */
            $content       = $contentMap[$tabId] ?? null;
            $hasContent    = $content && !$content->isEmpty();
            $canAddContent = $tab->canAddContent();

            // A tab is visible only when there is content or the visitor can contribute.
            if (!$hasContent && !$canAddContent) {
                continue;
            }

            $safeTitle = htmlspecialchars($tab->tab_title, ENT_QUOTES, 'UTF-8');
            $anchorId  = 'tab-schmitzit-' . (int) $tabId;

            // -----------------------------------------------------------------
            // Nav item
            // -----------------------------------------------------------------
            $navItems .= sprintf(
                '<li class="tabs-tab"><a href="#%s" class="tabs-tabLink" data-xf-click="tab">%s</a></li>',
                $anchorId,
                $safeTitle
            );

            // -----------------------------------------------------------------
            // Tab pane – render our sub-template
            // -----------------------------------------------------------------
            $paneHtml = $templater->renderTemplate(
                'public:schmitzit_custom_tab_content_pane',
                [
                    'tab'           => $tab,
                    'resource'      => $resource,
                    'content'       => $content,
                    'canAddContent' => $canAddContent,
                ]
            );

            $tabPanes .= sprintf(
                '<div class="block tabs-pane" id="%s">%s</div>',
                $anchorId,
                $paneHtml
            );
        }

        if ($navItems === '') {
            return;
        }

        // ---------------------------------------------------------------------
        // Inject nav items: find the closing </ul> of the resource tab-nav.
        // The XFRM resource view renders a <ul class="tabs  ..."> block.
        // We insert our <li> items just before the first </ul> that follows
        // the tab navigation landmark comment XF inserts, or – as a reliable
        // fallback – before the very first </ul> in the output.
        // ---------------------------------------------------------------------
        $navPattern = '/(<ul[^>]+class="[^"]*tabs[^"]*"[^>]*>.*?)(<\/ul>)/Us';
        if (preg_match($navPattern, $html, $m, PREG_OFFSET_CAPTURE)) {
            $insertPos = $m[2][1]; // position of </ul>
            $html = substr($html, 0, $insertPos) . $navItems . substr($html, $insertPos);
        }

        // ---------------------------------------------------------------------
        // Inject pane divs: append just before the closing wrapper div that
        // contains the tabs block. We look for the last </div> that closes
        // a "block tabs" container. Reliable anchor: the string
        // `data-xf-init="tabs"` always appears on the wrapper.
        // ---------------------------------------------------------------------
        $panesPattern = '/(data-xf-init="tabs"[^>]*>)(.*?)(<\/div>\s*<!-- end: tabs -->)/Us';
        if (preg_match($panesPattern, $html, $m2, PREG_OFFSET_CAPTURE)) {
            // Append after the last matched pane block inside the tabs wrapper.
            $endPos = $m2[3][1];
            $html   = substr($html, 0, $endPos) . $tabPanes . substr($html, $endPos);
        } else {
            // Fallback: just append panes right before </main> or at end of body.
            $html = str_replace('</main>', $tabPanes . '</main>', $html);
        }
    }
}
