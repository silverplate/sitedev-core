<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" indent="no" encoding="utf-8" />

	<xsl:include href="../common.xsl" />
	<xsl:include href="common.xsl" />

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
			</head>
			<body>
				<table width="100%" height="100%">
					<tr>
						<td height="99%" valign="top">
							<xsl:apply-templates select="system" mode="navigation" />

							<div id="content">
								<div id="title">
                                    <h1><xsl:call-template name="get-page-title" /></h1>
                                </div>

                                <xsl:apply-templates select="content" />
							</div>
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
</xsl:stylesheet>
