<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" indent="no" encoding="utf-8" />

	<xsl:include href="../common.xsl" />
	<xsl:include href="common.xsl" />
	<xsl:include href="modules.xsl" />
	<xsl:include href="forms.xsl" />

	<xsl:template match="page">
		<html>
			<head>
				<title>
					<xsl:if test="url/@path != '/cms/'">
						<xsl:call-template name="get-page-title" />
						<xsl:text> - </xsl:text>
					</xsl:if>
					<xsl:text>Система управления</xsl:text>
					<xsl:for-each select="system/title">
						<xsl:text> - </xsl:text>
						<xsl:value-of select="text()" disable-output-escaping="yes" />
					</xsl:for-each>
				</title>

				<link href="/cms/f/css/main.css" type="text/css" rel="stylesheet" />
				<link href="/cms/f/css/modules.css" type="text/css" rel="stylesheet" />
				<xsl:comment>[if IE]>&lt;link href="/cms/f/css/modules-ie.css" type="text/css" rel="stylesheet" />&lt;![endif]</xsl:comment>
				<link href="/cms/f/css/forms.css" type="text/css" rel="stylesheet" />

				<script src="/cms/f/js/module-documents.js" type="text/javascript" language="JavaScript" />
				<script src="/cms/f/js/cookies.js" type="text/javascript" language="JavaScript" />
				<script src="/cms/f/js/scripts.js" type="text/javascript" language="JavaScript" />
				<script src="/cms/f/js/tree.js" type="text/javascript" language="JavaScript" />
			</head>
			<body>
				<xsl:apply-templates select="content" />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="content">
		<div id="title">
			<xsl:choose>
				<xsl:when test="update-status">
					<table width="100%">
						<tr valign="top">
							<td width="99%"><h1><xsl:call-template name="get-page-title" /></h1></td>
							<td width="1%"><xsl:apply-templates select="update-status" /></td>
						</tr>
					</table>
				</xsl:when>
				<xsl:otherwise>
					<h1><xsl:call-template name="get-page-title" /></h1>
				</xsl:otherwise>
			</xsl:choose>
		</div>

		<xsl:apply-templates select="form|update-parent" />
	</xsl:template>

	<xsl:template match="update-parent">
		<xsl:if test="text()">
			<script type="text/javascript" language="JavaScript">
				<xsl:value-of select="concat('window.opener.', text())" />
			</script>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
