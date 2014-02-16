<?php

namespace Np\NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Np\NewsBundle\Entity\FeedRepository")
 */
class Feed
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_pull_time", type="integer")
     */
    private $lastPullTime;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Feed
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get lastPullTime
     *
     * @return integer
     */
    public function getLastPullTime()
    {
        return $this->lastPullTime;
    }

    /**
     * Set lastPullTime
     *
     * @param integer $lastPullTime
     * @return Feed
     */
    public function setLastPullTime($lastPullTime)
    {
        $this->lastPullTime = $lastPullTime;

        return $this;
    }

    public function getItemCandidates()
    {
        $rssContent = self::getContentByUrl($this->url);
        $xml = new \SimpleXMLElement($rssContent);
        $itemCandidates = array();
        foreach ($xml->xpath('//item') as $remoteItem) {
            $itemCandidates[] = $this->getCandidateByRemoteItem($remoteItem);
        }
        return $itemCandidates;
    }

    static private function getContentByUrl($url)
    {
        $curlHandler = curl_init($url);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($curlHandler);
        curl_close($curlHandler);
        return $content;
    }

    private function getCandidateByRemoteItem($remoteItem)
    {
        $itemCandidate = new FeedItem();
        $itemCandidate->setUrl((string) $remoteItem->link)
            ->setTitle((string) $remoteItem->title)
            ->setDescription((string) $remoteItem->description)
            ->setPublishTime(self::getUnixtimeByRssTime((string) $remoteItem->pubDate))
            ->setFeedId($this->id);
        return $itemCandidate;
    }

    static private function getUnixtimeByRssTime($rssTime)
    {
        return strtotime(trim(substr($rssTime, 0, strlen($rssTime) - 5)));
    }
}