<?xml version="1.0"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../../src/Ext/Xml/entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="no" encoding="utf-8" />

    <xsl:include href="../common.xsl" />
    <xsl:include href="common.xsl" />
    <xsl:include href="modules.xsl" />
    <xsl:include href="../../../templates/back/forms.xsl" />

    <xsl:template match="page">
        <html>
            <head>
                <title>
                    <xsl:if test="url[@path != '/cms/']">
                        <xsl:call-template name="get-page-title" />
                        <xsl:text> - </xsl:text>
                    </xsl:if>

                    <xsl:text>Система управления</xsl:text>

                    <xsl:text> - </xsl:text>
                    <xsl:value-of select="system/title" disable-output-escaping="yes" />
                </title>

                <link href="/cms/f/css/main.css" type="text/css" rel="stylesheet" />
                <link href="/cms/f/css/modules.css" type="text/css" rel="stylesheet" />
                <xsl:comment>[if IE]>&lt;link href="/cms/f/css/modules-ie.css" type="text/css" rel="stylesheet" />&lt;![endif]</xsl:comment>
                <link href="/cms/f/css/forms.css" type="text/css" rel="stylesheet" />
                <link href="/cms/f/calendar/calendar.css" type="text/css" rel="stylesheet" />

                <xsl:apply-templates
                    select="content/sys-head-css-files|content/sys-head-styles[text()]"
                    mode="sys"
                />

                <script src="/cms/f/js/jquery-1.6.4.min.js" type="text/javascript" />
                <script src="/cms/f/js/jquery-ui-1.8.16.custom.min.js" type="text/javascript" />
                <script src="/cms/f/js/cookies.js" type="text/javascript" />
                <script src="/cms/f/js/scripts.js" type="text/javascript" />
                <script src="/cms/f/js/common.js" type="text/javascript" />
                <script src="/cms/f/js/module-documents.js" type="text/javascript" />
                <script src="/cms/f/js/tree.js" type="text/javascript" />
                <script src="/cms/f/calendar/calendar.js" type="text/javascript" />
                <script src="/cms/f/js/filter.js" type="text/javascript" />

                <xsl:apply-templates
                    select="content/sys-head-js-files|content/sys-head-scripts[text()]"
                    mode="sys"
                />
            </head>
            <body>
                <div id="loading" />

                <table width="100%" height="100%">
                    <tr>
                        <td height="99%" valign="top">
                            <xsl:apply-templates select="system" mode="navigation" />
                            <xsl:apply-templates select="content" />
                        </td>
                    </tr>
                    <tr>
                        <td height="1%" valign="bottom">
                            <xsl:call-template name="page-footer" />
                        </td>
                    </tr>
                </table>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="*[starts-with(name(), 'sys-')]" />

    <xsl:template match="sys-head-styles" mode="sys">
        <style type="text/css">
            <xsl:value-of select="text()" disable-output-escaping="yes" />
        </style>
    </xsl:template>

    <xsl:template match="sys-head-css-files" mode="sys">
        <xsl:if test="text()">
            <xsl:call-template name="sys-css-file" />
        </xsl:if>
    </xsl:template>

    <xsl:template match="sys-head-css-files[file[text()]]" mode="sys">
        <xsl:apply-templates select="sys-head-css-files/file[text()]" />
    </xsl:template>

    <xsl:template match="sys-head-css-files/file" name="sys-css-file">
        <link href="{text()}" type="text/css" rel="stylesheet" />
    </xsl:template>

    <xsl:template match="sys-head-js-files" mode="sys">
        <xsl:if test="text()">
            <xsl:call-template name="sys-js-file" />
        </xsl:if>
    </xsl:template>

    <xsl:template match="sys-head-js-files[file[text()]]" mode="sys">
        <xsl:apply-templates select="sys-head-js-files/file[text()]" />
    </xsl:template>

    <xsl:template match="sys-head-js-files/file" name="sys-js-file">
        <script type="text/javascript" src="{text()}" />
    </xsl:template>

    <!-- <xsl:template match="sys-body-javascript" mode="sys">
        <script type="text/javascript">
            <xsl:value-of select="text()" disable-output-escaping="yes" />
        </script>
    </xsl:template> -->

    <xsl:template match="content">
        <div id="content">
            <xsl:choose>
                <xsl:when test="module">
                    <xsl:apply-templates select="module" />
                </xsl:when>
                <xsl:otherwise>
                    <div id="title"><h1>
                        <xsl:call-template name="get-page-title" />
                    </h1></div>

                    <xsl:apply-templates select="*[name() = 'update-status']" />
                    <xsl:apply-templates select="*[name() != 'update-status']" />
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>
</xsl:stylesheet>
