<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="*" mode="html">
		<xsl:element name="{name()}">
			<xsl:apply-templates select="@*" mode="html" />
			<xsl:apply-templates select="text() | *" mode="html" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="@*" mode="html">
		<xsl:attribute name="{name(.)}"><xsl:value-of select="." /></xsl:attribute>
	</xsl:template>

	<xsl:template match="text()" mode="html">
		<xsl:value-of select="." disable-output-escaping="yes" />
	</xsl:template>

	<xsl:template name="get-page-title">
		<xsl:choose>
			<xsl:when test="/node()/content/page-title/text()">
				<xsl:value-of select="/node()/content/page-title/text()" disable-output-escaping="yes" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="/node()/title/text()" disable-output-escaping="yes" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="get-page-url">
		<xsl:param name="is-http">true</xsl:param>

		<xsl:for-each select="/node()/url">
			<xsl:if test="$is-http = 'true'">http://</xsl:if>
			<xsl:value-of select="concat(@host, text())" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="get-date-period">
		<xsl:param name="start-year" />

		<xsl:if test="$start-year != /node()/date/@year">
			<xsl:value-of select="$start-year" />
			<xsl:text>&mdash;</xsl:text>
		</xsl:if>

		<xsl:value-of select="/node()/date/@year" />
	</xsl:template>

	<xsl:template match="html">
		<xsl:value-of select="text()" disable-output-escaping="yes" />
	</xsl:template>

    <xsl:template name="html-image">
		<xsl:param name="id" />
		<xsl:param name="alt" />
		<xsl:param name="title" />
		<xsl:param name="style" />

		<img src="{@uri}" width="{@width}" height="{@height}">
			<xsl:if test="$id != ''">
			    <xsl:attribute name="id"><xsl:value-of select="$id" /></xsl:attribute>
			</xsl:if>

            <xsl:if test="$alt != ''">
                <xsl:attribute name="alt"><xsl:value-of select="$alt" /></xsl:attribute>
            </xsl:if>

            <xsl:if test="$title != ''">
                <xsl:attribute name="title"><xsl:value-of select="$title" /></xsl:attribute>
            </xsl:if>

            <xsl:for-each select="@class">
                <xsl:copy select="." />
            </xsl:for-each>

            <xsl:if test="@style or $style != ''">
                <xsl:attribute name="style">
                    <xsl:value-of select="@style" />
                    <xsl:value-of select="$style" />
                </xsl:attribute>
            </xsl:if>
		</img>
    </xsl:template>

	<xsl:template match="image|illu">
		<xsl:param name="id" />
		<xsl:param name="alt" />
		<xsl:param name="title" />
		<xsl:param name="style" />

        <xsl:call-template name="html-image">
            <xsl:with-param name="id">
                <xsl:choose>
                    <xsl:when test="$id">
                        <xsl:value-of select="$id" />
                    </xsl:when>
                    <xsl:when test="@id">
                        <xsl:value-of select="@id" />
                    </xsl:when>
                </xsl:choose>
            </xsl:with-param>

            <xsl:with-param name="alt">
                <xsl:choose>
                    <xsl:when test="$alt">
                        <xsl:value-of select="$alt" />
                    </xsl:when>
                    <xsl:when test="alt">
                        <xsl:value-of select="alt" />
                    </xsl:when>
                    <xsl:when test="@alt">
                        <xsl:value-of select="@alt" />
                    </xsl:when>
                </xsl:choose>
            </xsl:with-param>

            <xsl:with-param name="title">
                <xsl:choose>
                    <xsl:when test="$title">
                        <xsl:value-of select="$title" />
                    </xsl:when>
                    <xsl:when test="title">
                        <xsl:value-of select="title" />
                    </xsl:when>
                    <xsl:when test="@title">
                        <xsl:value-of select="@title" />
                    </xsl:when>
                </xsl:choose>
            </xsl:with-param>

            <xsl:with-param name="style">
                <xsl:choose>
                    <xsl:when test="$style">
                        <xsl:value-of select="$style" />
                    </xsl:when>
                    <xsl:when test="@style">
                        <xsl:value-of select="@style" />
                    </xsl:when>
                </xsl:choose>
            </xsl:with-param>
        </xsl:call-template>
	</xsl:template>

	<xsl:template name="list-navigation">
		<xsl:param name="total" />
		<xsl:param name="per-page" />
		<xsl:param name="page" />
		<xsl:param name="type" />
		<xsl:param name="url-subquery" />
		<xsl:param name="separator" />
		<xsl:param name="is-tiny" />
		<xsl:param name="step" />
		<xsl:param name="js-func" />
		<xsl:param name="lang" />

		<xsl:variable name="total-pages" select="ceiling($total div $per-page)" />
		<xsl:variable name="item-separator">
			<xsl:choose>
				<xsl:when test="$separator">
					<span class="list-navigation-spacer"><xsl:value-of select="$separator" /></span>
				</xsl:when>
				<xsl:otherwise>&nbsp;</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="from" select="floor(($page - 1) div $step) * $step + 1" />

		<xsl:if test="$total-pages > 1">
			<div id="list-navigation">
				<xsl:if test="$page != 1 and $total-pages > $per-page">
					<a>
						<xsl:attribute name="href">
							<xsl:call-template name="list-navigation-url">
								<xsl:with-param name="page" select="1" />
								<xsl:with-param name="url-subquery" select="$url-subquery" />
								<xsl:with-param name="type" select="$type" />
								<xsl:with-param name="js-func" select="$js-func" />
							</xsl:call-template>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$is-tiny = 1">|&lt;</xsl:when>
							<xsl:when test="$lang = 'en'">First</xsl:when>
							<xsl:otherwise>В&nbsp;начало</xsl:otherwise>
						</xsl:choose>
					</a>
					<xsl:copy-of select="$item-separator" />
				</xsl:if>
				<xsl:if test="$page - $step &gt;= 1">
					<a>
						<xsl:attribute name="href">
							<xsl:call-template name="list-navigation-url">
								<xsl:with-param name="page" select="$page - $step" />
								<xsl:with-param name="url-subquery" select="$url-subquery" />
								<xsl:with-param name="type" select="$type" />
								<xsl:with-param name="js-func" select="$js-func" />
							</xsl:call-template>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$is-tiny = 1">&lt;&lt;</xsl:when>
							<xsl:otherwise>&minus;<xsl:value-of select="$step" /></xsl:otherwise>
						</xsl:choose>
					</a>
					<xsl:copy-of select="$item-separator" />
				</xsl:if>
				<xsl:if test="$page != 1">
					<a>
						<xsl:attribute name="href">
							<xsl:call-template name="list-navigation-url">
								<xsl:with-param name="page" select="$page - 1" />
								<xsl:with-param name="url-subquery" select="$url-subquery" />
								<xsl:with-param name="type" select="$type" />
								<xsl:with-param name="js-func" select="$js-func" />
							</xsl:call-template>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$is-tiny = 1">&lt;</xsl:when>
							<xsl:when test="$lang = 'en'">Previous</xsl:when>
							<xsl:otherwise>Предыдущая</xsl:otherwise>
						</xsl:choose>
					</a>
					<xsl:copy-of select="$item-separator" />
				</xsl:if>
				<xsl:call-template name="list-navigation-item">
					<xsl:with-param name="total" select="$total-pages" />
					<xsl:with-param name="page" select="1" />
					<xsl:with-param name="selected" select="$page" />
					<xsl:with-param name="from">
						<xsl:choose>
							<xsl:when test="$total-pages - $page &lt; $step and $from &gt; $step">
								<xsl:value-of select="$total-pages - $step" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$from - 1" />
							</xsl:otherwise>
						</xsl:choose>
					</xsl:with-param>
					<xsl:with-param name="type" select="$type" />
					<xsl:with-param name="url-subquery" select="$url-subquery" />
					<xsl:with-param name="separator" select="$separator" />
					<xsl:with-param name="step" select="$step" />
					<xsl:with-param name="js-func" select="$js-func" />
				</xsl:call-template>
				<xsl:if test="$page != $total-pages">
					<xsl:copy-of select="$item-separator" />
					<a>
						<xsl:attribute name="href">
							<xsl:call-template name="list-navigation-url">
								<xsl:with-param name="page" select="$page + 1" />
								<xsl:with-param name="url-subquery" select="$url-subquery" />
								<xsl:with-param name="type" select="$type" />
								<xsl:with-param name="js-func" select="$js-func" />
							</xsl:call-template>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$is-tiny = 1">&gt;</xsl:when>
							<xsl:when test="$lang = 'en'">Next</xsl:when>
							<xsl:otherwise>Следующая</xsl:otherwise>
						</xsl:choose>
					</a>
				</xsl:if>
				<xsl:if test="$total-pages - $page &gt;= $step">
					<xsl:copy-of select="$item-separator" />
					<a>
						<xsl:attribute name="href">
							<xsl:call-template name="list-navigation-url">
								<xsl:with-param name="page" select="$page + $step" />
								<xsl:with-param name="url-subquery" select="$url-subquery" />
								<xsl:with-param name="type" select="$type" />
								<xsl:with-param name="js-func" select="$js-func" />
							</xsl:call-template>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$is-tiny = 1">&gt;&gt;</xsl:when>
							<xsl:otherwise>+<xsl:value-of select="$step" /></xsl:otherwise>
						</xsl:choose>
					</a>
				</xsl:if>
				<xsl:if test="$total-pages != $page and $total-pages &gt; $per-page">
					<xsl:copy-of select="$item-separator" />
					<a>
						<xsl:attribute name="href">
							<xsl:call-template name="list-navigation-url">
								<xsl:with-param name="page" select="$total-pages" />
								<xsl:with-param name="url-subquery" select="$url-subquery" />
								<xsl:with-param name="type" select="$type" />
								<xsl:with-param name="js-func" select="$js-func" />
							</xsl:call-template>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$is-tiny = 1">&gt;|</xsl:when>
							<xsl:when test="$lang = 'en'">Last</xsl:when>
							<xsl:otherwise>В&nbsp;конец</xsl:otherwise>
						</xsl:choose>
					</a>
				</xsl:if>
			</div>
			<br style="clear: left" />
		</xsl:if>
	</xsl:template>

	<xsl:template name="list-navigation-item">
		<xsl:param name="total" />
		<xsl:param name="page" />
		<xsl:param name="selected" />
		<xsl:param name="from" />
		<xsl:param name="type" />
		<xsl:param name="separator" />
		<xsl:param name="url-subquery" />
		<xsl:param name="step" />
		<xsl:param name="js-func" />

		<xsl:variable name="current" select="$page + $from" />

		<xsl:choose>
			<xsl:when test="$selected = $current">
				<span class="list-navigation-selected"><xsl:value-of select="$current" /></span>
			</xsl:when>
			<xsl:otherwise>
				<a>
					<xsl:attribute name="href">
						<xsl:call-template name="list-navigation-url">
							<xsl:with-param name="page" select="$current" />
							<xsl:with-param name="type" select="$type" />
							<xsl:with-param name="url-subquery" select="$url-subquery" />
							<xsl:with-param name="js-func" select="$js-func" />
						</xsl:call-template>
					</xsl:attribute>
					<xsl:value-of select="$current" />
				</a>
			</xsl:otherwise>
		</xsl:choose>

		<xsl:if test="$page + $from &lt; $total and $page &lt; $step">
			<xsl:choose>
				<xsl:when test="$separator">
					<span class="list-navigation-spacer"><xsl:value-of select="$separator" /></span>
				</xsl:when>
				<xsl:otherwise>&nbsp;</xsl:otherwise>
			</xsl:choose>

			<xsl:call-template name="list-navigation-item">
				<xsl:with-param name="total" select="$total" />
				<xsl:with-param name="page" select="$page + 1" />
				<xsl:with-param name="selected" select="$selected" />
				<xsl:with-param name="from" select="$from" />
				<xsl:with-param name="type" select="$type" />
				<xsl:with-param name="separator" select="$separator" />
				<xsl:with-param name="url-subquery" select="$url-subquery" />
				<xsl:with-param name="step" select="$step" />
				<xsl:with-param name="js-func" select="$js-func" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<xsl:template name="list-navigation-url">
		<xsl:param name="page" />
		<xsl:param name="url-subquery" />
		<xsl:param name="type" />
		<xsl:param name="js-func" />

		<xsl:choose>
			<xsl:when test="$js-func != ''">
				<xsl:choose>
					<xsl:when test="contains($js-func, '~')">
						<xsl:text>javascript:</xsl:text>
						<xsl:value-of select="substring-before($js-func, '~')" />
						<xsl:value-of select="$page" />
						<xsl:value-of select="substring-after($js-func, '~')" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="concat('javascript:', $js-func, '(', $page, ')')" />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="$type = 'query'">
						<xsl:value-of select="concat('?p=', $page)" />
					</xsl:when>
					<xsl:when test="$type = 'url'">
						<xsl:value-of select="concat($page, '.html')" />
					</xsl:when>
				</xsl:choose>

				<xsl:if test="$url-subquery != ''">
					<xsl:choose>
						<xsl:when test="$type = 'query'">
							<xsl:variable name="replaced">
								<xsl:if test="$url-subquery != 'p'">
									<xsl:choose>
										<xsl:when test="contains($url-subquery, 'p=')">
											<xsl:value-of select="substring-before($url-subquery, 'p=')" />
											<xsl:variable name="tail" select="substring-after($url-subquery, 'p=')" />
											<xsl:if test="contains($tail, '&amp;')">
												<xsl:value-of select="substring-after($tail, '&amp;')" />
											</xsl:if>
										</xsl:when>
										<xsl:when test="contains($url-subquery, 'p&amp;')">
											<xsl:value-of select="concat(substring-before($url-subquery, 'p&amp;'),
												substring-after($url-subquery, 'p&amp;'))" />
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="$url-subquery" />
										</xsl:otherwise>
									</xsl:choose>
								</xsl:if>
							</xsl:variable>
							<xsl:if test="$replaced != ''">
								<xsl:value-of select="concat('&amp;', $replaced)" />
							</xsl:if>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="concat('?', $url-subquery)" />
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

    <xsl:template name="get-number-label">
        <xsl:param name="number" />
        <xsl:param name="case1" />
        <xsl:param name="case2" />
        <xsl:param name="case3" />

        <xsl:variable name="base"
                      select="$number - floor($number div 100) * 100" />

        <xsl:value-of select="concat($number, '&nbsp;')" />

        <xsl:choose>
            <xsl:when test="$base > 9 and $base &lt; 20">
                <xsl:value-of select="$case1" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:variable name="remainder"
                              select="$number - floor($number div 10) * 10" />
                <xsl:choose>
                    <xsl:when test="$remainder = 1">
                        <xsl:value-of select="$case2" />
                    </xsl:when>
                    <xsl:when test="$remainder > 0 and $remainder &lt; 5">
                        <xsl:value-of select="$case3" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$case1" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
