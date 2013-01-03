<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"
	            omit-xml-declaration="no"
	            indent="no"
	            encoding="utf-8"
	            doctype-system="http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" />

	<xsl:template match="page">
	    <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
	            xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                <xsl:apply-templates select="content/url" />
	    </urlset>
	</xsl:template>

    <xsl:template match="url">
        <xsl:element name="{name()}">
            <xsl:apply-templates mode="sitemap" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="*" mode="sitemap">
        <xsl:element name="{name()}">
            <xsl:value-of select="text()" disable-output-escaping="yes" />
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
