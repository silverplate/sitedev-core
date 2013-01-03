<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:import href="page.xsl" />

    <xsl:template match="content[sections-on-home-page]">
        <div id="content">
            <div id="title">
                <h1>
                    <xsl:call-template name="get-page-title" />
                </h1>
            </div>

            <xsl:apply-templates select="/node()/system/navigation" />
        </div>
    </xsl:template>

    <xsl:template match="navigation">
        <table class="cms-sections">
            <xsl:apply-templates
                select="back-section[position() mod 2 = 1]"
                mode="row"
            />
        </table>
    </xsl:template>

    <xsl:template match="back-section" mode="row">
        <tr>
            <xsl:if test="position() = last()">
                <xsl:attribute name="class">last</xsl:attribute>
            </xsl:if>

            <xsl:apply-templates select="self::node()|following-sibling::node()[1]" />
        </tr>
    </xsl:template>

    <xsl:template match="back-section">
        <td>
            <xsl:if test="position() = last() and position() != 1">
                <xsl:attribute name="class">last</xsl:attribute>
            </xsl:if>

            <xsl:apply-templates select="title" />
            <xsl:apply-templates select="description" />
        </td>
    </xsl:template>

    <xsl:template match="title">
        <a href="{../@uri}" class="title">
            <xsl:value-of select="text()" disable-output-escaping="yes" />
        </a>
    </xsl:template>

    <xsl:template match="description">
        <div class="description">
            <xsl:value-of select="text()" disable-output-escaping="yes" />
        </div>
    </xsl:template>
</xsl:stylesheet>
