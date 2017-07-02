<?php
/* 
 ** LASUHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for the Academic/Non Academic Staff Establishments of Lagos State University respectively . This Software has been tested on a remote server and is capable of encapsulating large information of the Lagos State University staff.
 * Copyright (C) 1983-2014 LASUHRM., http://www.lasu.edu.ng. Software Developed and re-engineered by OWOEYE OLUWATOBI MICHAEL, BSc. Computer Science.
 *
 *
 * 
 */

/**
 * Service Class for Performance Review
 *
 * @author orange
 */
class PerformanceKpiService extends BaseService {
	
	/**
	 * Get XML String from Kpi List
	 * @param $kpiList
	 * @return String
	 */
	public function getXmlFromKpi( $kpiList )
	{
		$xmlString	=	'';
		
		$performanceKpiList	=	$this->getKpiToPerformanceKpi( $kpiList );
		$xmlString			=	$this->getXml( $performanceKpiList );
		return $xmlString;
		
	}
	
	/**
	 * Get XML from Performance Kpi
	 * @param $performanceKpiList
	 * @return unknown_type
	 */
	public function getXml( $performanceKpiList)
	{
		try {
			$xmlStr = '
			<xml>
			</xml>';
	
	 
			$xml = simplexml_load_string($xmlStr);
			
			$kpis	=	$xml->addChild('kpis');
			$escapeHtml = array("&#039;" => "\'", "&" => "&amp;", "<" => "&lt;", ">" => "&gt;", "&#034;" => '\"');
			foreach( $performanceKpiList as $performanceKpi){
				$xmlKpi	=	$kpis->addChild('kpi');
				$xmlKpi->addChild('id',$performanceKpi->getId());
            $desc = $performanceKpi->getKpi();
            foreach($escapeHtml as $char => $str) {
               $desc = str_replace($char, $str, $desc);
            }
				$xmlKpi->addChild('desc',$desc);
				$xmlKpi->addChild('min',$performanceKpi->getMinRate());
				$xmlKpi->addChild('max',$performanceKpi->getMaxRate());
				$xmlKpi->addChild('rate',($performanceKpi->getRate()=='')?' ':$performanceKpi->getRate());
				$xmlKpi->addChild('comment',($performanceKpi->getComment()=='')?' ':$performanceKpi->getComment());
			}
			return $xml->asXML();
		}catch (Exception $e) {
			    throw new PerformanceServiceException($e->getMessage());
		}	  
	}
	
	/**
	 * Get Performance List from XML
	 * @param $xmlString
	 * @return unknown_type
	 */
	public function getPerformanceKpiList( $xmlString )
	{
		try {
			$performanceKpiList	=	array();
			
			$xml = simplexml_load_string($xmlString);
			foreach( $xml->kpis->kpi	as $kpi){
				$performanceKpi	=	new PerformanceKpi();
				$performanceKpi->setId((int)$kpi->id);
				$performanceKpi->setKpi((string)$kpi->desc);
				$performanceKpi->setMinRate((string)$kpi->min);
				$performanceKpi->setMaxRate((string)$kpi->max);
				$performanceKpi->setRate((string)$kpi->rate);
				$performanceKpi->setComment((string)$kpi->comment);
				array_push($performanceKpiList,$performanceKpi);
			}
			return $performanceKpiList;
		}catch (Exception $e) {
			throw new PerformanceServiceException($e->getMessage());
		}	  
		
	}
	
	/**
	 * Get Performance Kpi 
	 * @return unknown_type
	 */
	private function getKpiToPerformanceKpi( $kpiList)
	{
		try {
			
			$performanceKpiList	=	array();
			foreach ($kpiList as $kpi) {
				$performanceKpi	=	new PerformanceKpi();
				$performanceKpi->setId( $kpi->getId());
		    	$performanceKpi->setKpi( $kpi->getDesc());
		    	$performanceKpi->setMinRate( $kpi->getMin());
		    	$performanceKpi->setMaxRate( $kpi->getMax());
		    	array_push($performanceKpiList,$performanceKpi);
			}
			return $performanceKpiList;
		} catch (Exception $e) {
		    throw new PerformanceServiceException($e->getMessage());
		}	    
	}


}