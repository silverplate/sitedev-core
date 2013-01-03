<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!--xsl:output method="html" indent="no" encoding="utf-8" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd" /-->
	<xsl:output method="xml"
	            omit-xml-declaration="yes"
	            indent="no"
	            encoding="utf-8"
	            doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	            doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />

	<xsl:include href="common.xsl" />
	<xsl:include href="fo-common.xsl" />

	<xsl:template match="page|page-not-found">
		<html xml:lang="ru" lang="ru">
		<!--html-->
			<head>
				<!--link href="/favicon.ico" type="image/x-icon" rel="shortcut icon" />
				<link href="/favicon.ico" type="image/x-icon" rel="icon" /-->

				<title>
					<xsl:call-template name="get-page-title" />
					<xsl:if test="url/@path != '/'">
						<xsl:for-each select="system/navigation/main/item[@uri = '/']">
							<xsl:text> - </xsl:text>
							<xsl:value-of select="title/text()" disable-output-escaping="yes" />
						</xsl:for-each>
					</xsl:if>
				</title>

				<!--link rel="stylesheet" rev="stylesheet" href="/f/css/common.css" />
				<link rel="stylesheet" rev="stylesheet" href="/f/css/screen.css" />
				<script type="text/javascript" language="JavaScript" src="/f/js/scripts.js"></script-->
			</head>
			<body>
				<xsl:apply-templates select="content" />
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
