<?php

namespace Imdb;

/**
 * Search for people on IMDb
 * @author Izzy (izzysoft AT qumran DOT org)
 * @copyright 2008-2009 by Itzchak Rehberg and IzzySoft
 */
class PersonSearch extends MdbBase
{
    private $name = null;

    /**
     * Search for people on imdb who match $searchTerms
     * @param string $searchTerms
     * @return Person[]
     */
    public function search($searchTerms)
    {
        $this->setsearchname($searchTerms);
        return $this->results();
    }

    /**
     * Set the name (title) to search for
     * @param string $name what to search for - (part of) the movie name
     */
    public function setsearchname($name)
    {
        $this->name = $name;
    }

    /**
     * This does nothing
     * @deprecated
     */
    public function reset()
    {
    }

    /**
     * Setup search results
     * @return Person[]
     */
    public function results()
    {
        $page = $this->getPage();
        $results = array();

        if (preg_match_all(
            // make sure to catch col #3, not #1 (pic only)
            //         photo           name                   1=id        2=name        3=details
            '|<tr.*>\s*<td.*>.*</td>\s*<td.*<a href="/name/nm(\d+?)[^>]*>([^<]+)</a>\s*(.*)</td>|Uims',
            $page,
            $matches
        )) {
            $mc = count($matches[0]);
            $mids_checked = array();

            $this->logger->debug("[Person Search] $mc matches");

            for ($i = 0; $i < $mc; ++$i) {
                $pid = $matches[1][$i];

                if (in_array($pid, $mids_checked)) {
                    continue;
                }

                $mids_checked[] = $pid;
                $name = $matches[2][$i];
                $info = $matches[3][$i];
                $person = Person::fromSearchResults($pid, $name, $this->config, $this->logger, $this->cache);

                if (!empty($info)) {
                    if (preg_match(
                        '|<small>\((.*),\s*<a href="/title/tt(\d{7,8}).*"\s*>(.*)</a>\s*\((\d{4})\)\)|Ui',
                        $info,
                        $match
                    )) {
                        $role = $match[1];
                        $mid = $match[2];
                        $movie = $match[3];
                        $year = $match[4];
                        $person->setSearchDetails($role, $mid, $movie, $year);
                    }
                }

                $results[] = $person;
                unset($person);
            }
        } else {
            $xpath = $this->getXpathPage($this->name);
            $cells = $xpath->query("//section[@data-testid='find-results-section-name']//div[@class='ipc-metadata-list-summary-item__tc']");

            foreach ($cells as $cell) {
                $linkAndName = $xpath->query('.//a[@class="ipc-metadata-list-summary-item__t"]', $cell);

                if ($linkAndName->length < 1 || !preg_match('!nm(?<pid>\d+)!', $linkAndName->item(0)->getAttribute('href'), $href)) {
                    continue;
                }

                $person = Person::fromSearchResults(
                    $href['pid'],
                    trim($linkAndName->item(0)->nodeValue),
                    $this->config,
                    $this->logger,
                    $this->cache
                );

                $results[] = $person;
                unset($person);
            }
        }

        return $results;
    }

    /**
     * Create the IMDB URL for the name search
     * @return string url
     */
    protected function buildUrl($context = null)
    {
        return "https://" . $this->imdbsite . "/find?q=" . urlencode($this->name) . "&s=nm";
    }
}
