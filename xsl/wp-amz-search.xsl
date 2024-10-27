<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:imol="http://webservices.amazon.com/AWSECommerceService/2007-10-29">

<xsl:output method="html" indent="no"/>

<xsl:variable name="ImgSize">
     <xsl:value-of select="//imol:Arguments/imol:Argument[@Name = 'ImgSize']/@Value"/>
</xsl:variable> 

<xsl:template match="imol:ItemSearchResponse">
<table>
	<tr>
		<td width="100%" class="num-results">
		<img>
			<xsl:attribute name="src"><xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'Flag']/@Value" /></xsl:attribute>
			<xsl:attribute name="border">0</xsl:attribute>
			<xsl:attribute name="width">24</xsl:attribute>
			<xsl:attribute name="height">13</xsl:attribute>
			<xsl:attribute name="alt">flag</xsl:attribute>
		</img>
		
		<b><xsl:value-of select="imol:Items/imol:TotalResults" /> 
            <xsl:choose>
                <xsl:when test="imol:Items/imol:TotalResults = 1"> result</xsl:when>
                <xsl:otherwise> results</xsl:otherwise>
            </xsl:choose>
		   for 
			"<i><xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'Keywords']/@Value" /></i>"
            on <xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'ServerName']/@Value" /></b>
		</td>
	</tr>
	
	<xsl:apply-templates select="imol:Items/imol:Request/imol:ItemSearchRequest">
        <xsl:with-param name="TotalPages"><xsl:value-of select="imol:Items/imol:TotalPages" /></xsl:with-param> 
        <xsl:with-param name="SearchURL">?amz_search=1&amp;a_server=<xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'a_server']/@Value" />&amp;category=<xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'SearchIndex']/@Value" />&amp;field-keywords=<xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'keyword']/@Value" /></xsl:with-param> 
    </xsl:apply-templates>
	
	<xsl:apply-templates select="imol:Items/imol:Item" />
	
	<xsl:apply-templates select="imol:Items/imol:Request/imol:ItemSearchRequest">
        <xsl:with-param name="TotalPages"><xsl:value-of select="imol:Items/imol:TotalPages" /></xsl:with-param> 
        <xsl:with-param name="SearchURL">?amz_search=1&amp;a_server=<xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'a_server']/@Value" />&amp;category=<xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'SearchIndex']/@Value" />&amp;field-keywords=<xsl:value-of select="imol:OperationRequest/imol:Arguments/imol:Argument[@Name = 'keyword']/@Value" /></xsl:with-param> 
    </xsl:apply-templates>

</table>
</xsl:template>

<xsl:template match="imol:Items/imol:Request/imol:ItemSearchRequest">
	<xsl:param name="TotalPages"></xsl:param> 
	<xsl:param name="SearchURL"></xsl:param>
	<xsl:if test="$TotalPages &gt; 0">
	<tr>
        <td class="paging">
			<xsl:choose>
                <xsl:when test="$TotalPages = 1">
                     Page 1 of 1
               </xsl:when>
                <xsl:otherwise>
                    <xsl:choose>
                        <xsl:when test="imol:ItemPage &gt; 1">
                            [<a>
                                <xsl:attribute name="href"><xsl:value-of select="$SearchURL"/>&amp;ItemPage=<xsl:value-of select="imol:ItemPage - 1"/></xsl:attribute>
                            &lt; prev
                            </a>]&#160;&#160;
                        </xsl:when>
                        <xsl:otherwise>
                            [&lt; prev]&#160;&#160;
                        </xsl:otherwise>
                    </xsl:choose>
                    
                    Page <xsl:value-of select="imol:ItemPage" /> of <xsl:value-of select="$TotalPages"/>
        
                    <xsl:choose>
                        <xsl:when test="imol:ItemPage &lt; $TotalPages">
                            &#160;&#160;[<a>
                                <xsl:attribute name="href"><xsl:value-of select="$SearchURL"/>&amp;ItemPage=<xsl:value-of select="imol:ItemPage + 1"/></xsl:attribute>
                            next &gt;
                            </a>]
                        </xsl:when>
                        <xsl:otherwise>
                            &#160;&#160;[next &gt;]
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
			</xsl:choose>
        </td>
	</tr>
	</xsl:if>
</xsl:template>


<xsl:template match="imol:Items/imol:Item">
      <tr>
        <td width="100%" class="separator">
          <table class="item">
            <tr>
				<td valign="top">
				<xsl:choose>
					<xsl:when test="$ImgSize = 'Small'">
						<xsl:apply-templates select="imol:SmallImage">
							<xsl:with-param name="detailURL"><xsl:value-of select="imol:DetailPageURL" /></xsl:with-param>
							<xsl:with-param name="altText"><xsl:value-of select="imol:ItemAttributes/imol:Title" /></xsl:with-param> 
						</xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$ImgSize = 'Medium'">
						<xsl:apply-templates select="imol:MediumImage">
							<xsl:with-param name="detailURL"><xsl:value-of select="imol:DetailPageURL" /></xsl:with-param>
							<xsl:with-param name="altText"><xsl:value-of select="imol:ItemAttributes/imol:Title" /></xsl:with-param> 
						</xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$ImgSize = 'Large'">
						<xsl:apply-templates select="imol:LargeImage">
							<xsl:with-param name="detailURL"><xsl:value-of select="imol:DetailPageURL" /></xsl:with-param>
							<xsl:with-param name="altText"><xsl:value-of select="imol:ItemAttributes/imol:Title" /></xsl:with-param> 
						</xsl:apply-templates>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text> </xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				</td>

				<td valign="top">
                <a>
                	<xsl:attribute name="href"><xsl:value-of select="imol:DetailPageURL" /></xsl:attribute>
                	<xsl:attribute name="target">_blank</xsl:attribute>
                	<xsl:value-of select="imol:ItemAttributes/imol:Title" />
                </a>
                
                <br/>
                
				<span class="item-info">
					<xsl:if test="imol:ItemAttributes/imol:Director">
					  directed by <xsl:value-of select="imol:ItemAttributes/imol:Director" /><br/>
					</xsl:if>
					
					<xsl:if test="imol:ItemAttributes/imol:Actor">
					  starring: 
                        <xsl:for-each select="imol:ItemAttributes/imol:Actor">
                            <xsl:value-of select="."/> <xsl:if test="position()!=last()">, </xsl:if> 
                        </xsl:for-each>
                        <br/>
					</xsl:if>
					
					<xsl:if test="imol:ItemAttributes/imol:Author">
					  by <xsl:value-of select="imol:ItemAttributes/imol:Author" /><br/>
					</xsl:if>
					
					<xsl:if test="imol:ItemAttributes/imol:Artist">
					  by <xsl:value-of select="imol:ItemAttributes/imol:Artist" /><br/>
					</xsl:if>
					
					<xsl:if test="imol:ItemAttributes/imol:Manufacturer">
					  by <xsl:value-of select="imol:ItemAttributes/imol:Manufacturer" /><br/>
					</xsl:if>
					
					<xsl:if test="imol:ItemAttributes/imol:Binding">
					  <xsl:value-of select="imol:ItemAttributes/imol:Binding" /><br/>
					</xsl:if>
					
					<xsl:if test="imol:ItemAttributes/imol:Platform">
                      <xsl:for-each select="imol:ItemAttributes/imol:Platform">
                           <xsl:sort select="." />
                           <xsl:value-of select="." /> <xsl:if test="position()!=last()">, </xsl:if> 
                        </xsl:for-each>
                        <br/>
					</xsl:if>
					
					<xsl:if test="imol:SalesRank">
					  <br/>Sales rank: <xsl:value-of select="imol:SalesRank" /><br/>
					</xsl:if>
             					
					<xsl:if test="imol:ItemAttributes/imol:ListPrice/imol:FormattedPrice">
						<br/>List Price: <span class="list-price-price"><xsl:value-of select="imol:ItemAttributes/imol:ListPrice/imol:FormattedPrice" /></span>
					</xsl:if>
					<xsl:if test="imol:OfferSummary/imol:LowestNewPrice/imol:FormattedPrice">
						<br/>Lowest Price: <span class="lowest-price-price"><xsl:value-of select="imol:OfferSummary/imol:LowestNewPrice/imol:FormattedPrice" /></span>
					</xsl:if>
 				</span>
             </td>
            </tr>
          </table>
        </td>
      </tr>
</xsl:template>

<xsl:template match="imol:SmallImage">
	<xsl:param name="detailURL"></xsl:param> 
	<xsl:param name="altText"></xsl:param> 
	<xsl:if test="imol:URL">
		<a>
			<xsl:attribute name="href"><xsl:value-of select="$detailURL"/></xsl:attribute>
			<xsl:attribute name="target">_blank</xsl:attribute>
			<img>
				<xsl:attribute name="src"><xsl:value-of select="imol:URL" /></xsl:attribute>
				<xsl:attribute name="border">0</xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="imol:Width" /></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="imol:Height" /></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="$altText" /></xsl:attribute>
				<xsl:attribute name="title"><xsl:value-of select="$altText" /></xsl:attribute>
			</img>
		</a>
	</xsl:if>
</xsl:template>

<xsl:template match="imol:MediumImage">
	<xsl:param name="detailURL"></xsl:param> 
	<xsl:param name="altText"></xsl:param> 
	<xsl:if test="imol:URL">
		<a>
			<xsl:attribute name="href"><xsl:value-of select="$detailURL"/></xsl:attribute>
			<xsl:attribute name="target">_blank</xsl:attribute>
			<img>
				<xsl:attribute name="src"><xsl:value-of select="imol:URL" /></xsl:attribute>
				<xsl:attribute name="border">0</xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="imol:Width" /></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="imol:Height" /></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="$altText" /></xsl:attribute>
				<xsl:attribute name="title"><xsl:value-of select="$altText" /></xsl:attribute>
			</img>
		</a>
	</xsl:if>
</xsl:template>

<xsl:template match="imol:LargeImage">
	<xsl:param name="detailURL"></xsl:param> 
	<xsl:param name="altText"></xsl:param> 
	<xsl:if test="imol:URL">
		<a>
			<xsl:attribute name="href"><xsl:value-of select="$detailURL"/></xsl:attribute>
			<xsl:attribute name="target">_blank</xsl:attribute>
			<img>
				<xsl:attribute name="src"><xsl:value-of select="imol:URL" /></xsl:attribute>
				<xsl:attribute name="border">0</xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="imol:Width" /></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="imol:Height" /></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="$altText" /></xsl:attribute>
				<xsl:attribute name="title"><xsl:value-of select="$altText" /></xsl:attribute>
			</img>
		</a>
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
