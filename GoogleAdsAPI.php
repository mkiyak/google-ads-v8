<?php

use Google\Ads\GoogleAds\Lib\V9\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V9\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Util\FieldMasks;
use Google\Ads\GoogleAds\Util\V9\ResourceNames;
use Google\Ads\GoogleAds\V9\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V9\Resources\Campaign;
use Google\Ads\GoogleAds\V9\Services\CampaignOperation;
use Google\Ads\GoogleAds\V9\Services\GoogleAdsRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GoogleAdsAPI
{
    private const BUDGETMULTIPLIER = 1000000;

    public static function showReportAction(GoogleAdsClient $googleAdsClient, $customerId, $query, $options = [])
    {
        $response = $googleAdsClient->getGoogleAdsServiceClient()->search( $customerId,  $query,  ['returnTotalResultsCount' => true] );

        $results = [];
        foreach ($response->getPage()->getIterator() as $googleAdsRow) {
            $results[] = json_decode($googleAdsRow->serializeToJsonString(), true);
        }
        return $results;
    }

    public static function costMicros(GoogleAdsClient $googleAdsClient, $customerId) // Müşteri toplam maliyetini döndürmektedir.
    {
        try {
            $query = self::select("customer", ["customer.id", "metrics.cost_micros", "metrics.cost_per_all_conversions"]);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            $cost = 0;
            foreach($response as $key => $item) {
                $cost += $item["metrics"]["costMicros"];
            }
            $cost = intval($cost) / floatval(self::BUDGETMULTIPLIER);
        } catch (\Exception $e) {
            $cost = 0;
        }
        return $cost;
    }

    public static function customer(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $orderBy = "", $limit = 10000) // Müşteriye ait toplam tıklanma, maliyet vs verileri döndürür
    {
        $response = [];
//        try {
            $fields = [
                'metrics.average_cost',
                'metrics.average_cpc',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.ctr',
                'metrics.impressions',
                'segments.date',
                'metrics.invalid_clicks',
                'metrics.cost_micros',
            ];

//            if($during) $options[] = " segments.date DURING $during";

            $where = implode(" AND ", $options);

            $query = self::select("customer", $fields, $where, $orderBy, $limit);
//            dd($query);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);

//        } catch (\Exception $e) {
//
//        }
        return $response;
    }

    public static function AdGroups(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $limit = 10000, $orderBy = "") // Reklam gruplarına ilişkin verileri döndürür
    {
        $result = [];
        try {
            $fields = [
                "ad_group.campaign",
                "ad_group.id",
                "ad_group.labels",
                "ad_group.name",
                "ad_group.status",
                "ad_group.resource_name",
                "ad_group.percent_cpc_bid_micros",
                "ad_group.type",
                "ad_group.cpc_bid_micros",
                "metrics.cost_per_all_conversions",
                "metrics.clicks",
                "metrics.cost_micros",
                "metrics.average_cpc",
                "metrics.average_cost",
                "metrics.average_cpe",
                "metrics.conversions",
                "metrics.cost_per_conversion",
                "metrics.impressions",
                "metrics.interaction_rate",
                "metrics.interactions",
                "metrics.conversions_value",
                "metrics.average_cpm",
                "metrics.cost_per_conversion",
                "campaign.id",
                "campaign.name",
                "campaign.status",
                "ad_group.cpc_bid_micros"
            ];

            if(!$during) {
                $fields[] = 'segments.date';
            }

            if($during && $during != 'ALL') $options[] = " segments.date DURING $during";

            $options[] = " ad_group.status IN ('ENABLED', 'PAUSED')";

            $where = implode(" AND ", $options);
            $query = self::select("ad_group", $fields, $where, $orderBy, $limit);
//            dd($query);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    public static function AdGroupAd(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [],  $limit = 10000, $orderBy = "") // Reklam gruplarına ilişkin reklam verileri döndürür
    {
        $result = [];
        try {
            $fields = [
                'campaign.id',
                'campaign.name',
                'ad_group.id',
                'ad_group.name',
                'ad_group.status',
                'ad_group_ad.ad.expanded_text_ad.description',
                'ad_group_ad.ad.expanded_text_ad.description2',
                'ad_group_ad.ad.expanded_text_ad.headline_part1',
                'ad_group_ad.ad.expanded_text_ad.headline_part2',
                'ad_group_ad.ad.expanded_text_ad.headline_part3',
                'ad_group_ad.ad.final_urls',
                'ad_group_ad.ad.call_ad.headline2',
                'ad_group_ad.ad.call_ad.headline1',
                'ad_group_ad.ad.call_ad.description2',
                'ad_group_ad.ad.call_ad.description1',
                'ad_group_ad.ad.app_ad.images',
                'ad_group_ad.ad.app_ad.headlines',
                'ad_group_ad.ad.app_ad.descriptions',
                'ad_group_ad.ad.app_ad.html5_media_bundles',
                'ad_group_ad.ad.call_ad.path1',
                'ad_group_ad.ad.call_ad.path2',
                'ad_group_ad.ad.responsive_search_ad.path1',
                'ad_group_ad.ad.responsive_search_ad.path2',
                'ad_group_ad.ad.responsive_search_ad.headlines',
                'ad_group_ad.ad.responsive_search_ad.descriptions',
                'ad_group_ad.ad.text_ad.description2',
                'ad_group_ad.ad.text_ad.description1',
                'ad_group_ad.ad.text_ad.headline',
                'ad_group_ad.ad.type',
                'ad_group_ad.labels',
                'ad_group_ad.status',
                'ad_group_ad.resource_name',
                'ad_group_ad.ad.image_ad.preview_pixel_width',
                'ad_group_ad.ad.image_ad.preview_pixel_height',
                'ad_group_ad.ad.image_ad.preview_image_url',
                'ad_group_ad.ad.image_ad.pixel_width',
                'ad_group_ad.ad.image_ad.pixel_height',
                'ad_group_ad.ad.image_ad.name',
                'ad_group_ad.ad.image_ad.image_url',
                'ad_group_ad.ad.id',
                'metrics.average_cost',
                'metrics.average_cpc',
                'metrics.average_cpe',
                'metrics.average_cpm',
                'metrics.average_cpv',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.cost_micros',
                'metrics.interactions',
                'metrics.impressions',
            ];

            if(!$during) {
                $fields[] = 'segments.date';
            }

            if($during && $during != 'ALL') $options[] = " segments.date DURING $during";

            $options[] = " ad_group_ad.status != 'REMOVED'";

            $where = implode(" AND ", $options);
            $query = self::select("ad_group_ad", $fields, $where, "", $limit);

            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    public static function Campaigns(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $limit = 10000, $orderBy = "") // Kampanya verilerini döndürür
    {
        $result = [];
        try {
            $fields = [
                'campaign.id',
                'campaign.labels',
                'campaign.name',
                'campaign.status',
                'metrics.average_cpc',
                'metrics.average_cost',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.cost_micros',
                'metrics.ctr',
                'metrics.invalid_clicks',
                'metrics.impressions',
                'campaign_budget.type',
                'campaign_budget.total_amount_micros',
                'campaign_budget.status',
                'campaign_budget.resource_name',
                'campaign_budget.name',
                'campaign_budget.period',
                'campaign_budget.id',
                'campaign_budget.reference_count',
                'campaign_budget.recommended_budget_estimated_change_weekly_views',
                'campaign_budget.recommended_budget_estimated_change_weekly_interactions',
                'campaign_budget.recommended_budget_estimated_change_weekly_cost_micros',
                'campaign_budget.recommended_budget_estimated_change_weekly_clicks',
                'campaign_budget.recommended_budget_amount_micros',
                'campaign_budget.has_recommended_budget',
                'campaign_budget.explicitly_shared',
                'campaign_budget.delivery_method',
                'campaign_budget.amount_micros',
                'metrics.interactions',
                'metrics.interaction_rate',
                'metrics.average_page_views',
            ];

            if($during == "")
                $fields[] = 'segments.date';

            if($during && $during != 'ALL') $options[] = " segments.date DURING $during";

            $options[] = " campaign.status != 'REMOVED'";

            $where = implode(" AND ", $options);

            $query = self::select("campaign", $fields, $where, $orderBy, $limit);
//            dump($query);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    public static function Keywords(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $negative = "FALSE", $limit = 10000, $orderBy = "") // Anahtar kelimelere reklam verileri döndürür
    {
        $result = [];
        try {
            $fields = [
                'ad_group.id',
                'ad_group.name',
                'ad_group.status',
                'keyword_view.resource_name',
                'campaign.id',
                'campaign.name',
                'campaign.status',
                'metrics.average_cost',
                'metrics.average_cpc',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.ctr',
                'metrics.cost_micros',
                'metrics.interactions',
                'metrics.impressions',
                'metrics.historical_search_predicted_ctr',
                'metrics.historical_quality_score',
                'metrics.historical_landing_page_quality_score',
                'metrics.historical_creative_quality_score',
                'ad_group_criterion.negative',
                'ad_group_criterion.keyword.match_type',
                'ad_group_criterion.approval_status',
                'ad_group_criterion.ad_group',
                'ad_group_criterion.final_urls',
                'ad_group_criterion.final_url_suffix',
                'ad_group_criterion.final_mobile_urls',
                'ad_group_criterion.cpc_bid_micros',
                'ad_group_criterion.quality_info.quality_score',
                'ad_group_criterion.quality_info.creative_quality_score',
                'ad_group_criterion.keyword.text',
                'ad_group_criterion.labels',
                'ad_group_criterion.display_name',
                'ad_group_criterion.disapproval_reasons',
                'ad_group_criterion.status',
                'ad_group_criterion.type',
                'ad_group_criterion.url_custom_parameters',
                'ad_group_criterion.webpage.criterion_name',
                'ad_group_criterion.resource_name',
                'ad_group_criterion.listing_group.type',
                'ad_group_criterion.criterion_id',
                'ad_group_criterion.position_estimates.estimated_add_cost_at_first_position_cpc',
                'ad_group_criterion.age_range.type',
                'ad_group_criterion.gender.type',
            ];

            $options[] = " ad_group_criterion.negative=$negative";

            if($during == "")
                $fields[] = 'segments.date';

            if($during && $during != 'ALL') $options[] = " segments.date DURING $during";

            $options[] = " ad_group_criterion.status != 'REMOVED'";

            $where = implode(" AND ", $options);

            $query = self::select("keyword_view", $fields, $where, $orderBy, $limit);
//            dd($query);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    public static function SearchTerms(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $limit = 10000, $orderBy = "") // Arama Terimlerine ilişkin reklam verileri döndürür
    {
        $result = [];
        try {
            $fields = [
                'search_term_view.ad_group',
                'search_term_view.resource_name',
                'search_term_view.search_term',
                'search_term_view.status',
                'metrics.average_cpc',
                'metrics.average_cost',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.cost_micros',
                'metrics.ctr',
                'metrics.impressions',
                'metrics.interactions',
                'metrics.cost_per_conversion',
                'ad_group.name',
                'ad_group.labels',
                'ad_group.id',
                'ad_group.status',
                'ad_group.type',
                'campaign.id',
                'campaign.labels',
                'campaign.name',
                'campaign.resource_name',
                'campaign.status',
                'segments.search_term_match_type',
            ];
            if($during == "")
                $fields[] = 'segments.date';

            if($during && $during != 'ALL') $options[] = " segments.date DURING $during";

            $where = implode(" AND ", $options);

            $query = self::select("search_term_view", $fields, $where, $orderBy, $limit);

            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    public static function ReportGeoPerformance(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $limit = 10000, $orderBy = "") // Coğrafi raporlama verilerini döndürür
    {
        $result = [];
        try {
            $fields = [
                'geographic_view.country_criterion_id',
                'geographic_view.location_type',
                'geographic_view.resource_name',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.cost_micros',
                'metrics.ctr',
                'metrics.impressions',
                'metrics.interaction_rate',
                'metrics.interactions',
                'customer.id',
                'segments.geo_target_city',
                'segments.device',
                'metrics.average_cpc',
                'segments.day_of_week',
            ];
//            if($during) $options[] = " segments.date DURING $during";

            $where = implode(" AND ", $options);

            $query = self::select("geographic_view", $fields, $where, $orderBy, $limit);
//            dd($query);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    public static function ReportKeywordsPerformance(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $limit = 10000, $orderBy = "") // Anahtar Kelime performans raporlama verilerini döndürür
    {
        $result = [];
        try {
            $fields = [
                'campaign.id',
                'campaign.name',
                'ad_group.id',
                'ad_group.name',
                'ad_group_criterion.criterion_id',
                'ad_group_criterion.keyword.text',
                'ad_group_criterion.keyword.match_type',
                'metrics.impressions',
                'metrics.clicks',
                'metrics.cost_micros',
            ];
            if($during) $options[] = " segments.date DURING $during";

            $where = implode(" AND ", $options);

            $query = self::select("keyword_view", $fields, $where, $orderBy, $limit);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);
            foreach($response as $key => $item) {
                $result[] = $item;
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    protected static function select($table, $fields, $where = null, $orderby = null, $limit = null)
    {
        $query = "SELECT ".implode(',', $fields)." FROM ".$table;
        $where ? $query .= " WHERE ".$where : "";
        $orderby ? $query .= " ORDER BY ".$orderby : "";
        $limit ? $query .= " LIMIT ".$limit : "";

        return $query;
    }

    public static function customerAndDevicePerformans(GoogleAdsClient $googleAdsClient, $customerId, $during = "", $options = [], $orderBy = "", $limit = 10000) // Müşteriye ait tüm işlemlerde ki cihazlara ait verileri döndürür
    {
        $response = [];
        try {
            $fields = [
                'metrics.clicks',
                'metrics.conversions',
                'metrics.impressions',
                'metrics.cost_micros',
                'segments.date',
                'segments.device',
            ];

//            if($during) $options[] = " segments.date DURING $during";

            $where = implode(" AND ", $options);

            $query = self::select("customer", $fields, $where, $orderBy, $limit);
            $response = self::showReportAction($googleAdsClient, $customerId, $query);

        } catch (\Exception $e) {

        }
        return $response;
    }

}
