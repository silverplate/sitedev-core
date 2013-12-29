<?xml version="1.0"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../library/Ext/Xml/entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output
        method="html"
        indent="no"
        encoding="utf-8"
        doctype-system="about:legacy-compat"
    />

    <xsl:include href="common.xsl" />
    <!-- <xsl:include href="site-common.xsl" /> -->

    <xsl:template match="page|page-not-found">
        <html>
            <head>
                <title>
                    <xsl:call-template name="get-page-title" />

                    <xsl:if test="url/@path != '/'">
                        <xsl:for-each select="system/navigation/main/item[@uri = '/']">
                            <xsl:text> &mdash; </xsl:text>
                            <xsl:value-of select="title" disable-output-escaping="yes" />
                        </xsl:for-each>
                    </xsl:if>
                </title>

                <!--
                <link href="/favicon.ico" type="image/x-icon" rel="shortcut icon" />
                <link href="/favicon.ico" type="image/x-icon" rel="icon" />
                <link href="/f/css/common.css" rel="stylesheet" rev="stylesheet" />
                <link href="/f/css/screen.css" rel="stylesheet" rev="stylesheet" />
                <script src="/f/js/scripts.js" type="text/javascript" />
                -->
            </head>
            <body>
                <xsl:apply-templates select="content" />
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
