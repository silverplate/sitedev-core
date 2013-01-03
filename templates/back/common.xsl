<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template name="module-add-element-link">
		<xsl:param name="label">Добавить</xsl:param>
		<xsl:param name="uri">./?add</xsl:param>

		<xsl:call-template name="module-element-link">
			<xsl:with-param name="label" select="$label" />
			<xsl:with-param name="uri" select="$uri" />
			<xsl:with-param name="is-selected"><xsl:choose>
				<xsl:when test="ancestor-or-self::node()[name() = 'module'][@is-new]">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose></xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="module-element-link">
		<xsl:param name="label" />
		<xsl:param name="uri" />
		<xsl:param name="is-selected">false</xsl:param>

		<a href="{$uri}">
			<xsl:attribute name="class">
				<xsl:text>add-element</xsl:text>
				<xsl:if test="$is-selected = 'true'"> selected</xsl:if>
			</xsl:attribute>

			<xsl:value-of select="$label" disable-output-escaping="yes" />
		</a>
	</xsl:template>

	<xsl:template
        match="local-navigation[@type = 'filter' or @type = 'content-filter']"
        mode="list"
    >
		<script type="text/javascript" src="/cms/f/js/filter.js" />

		<div id="filter-link">
			<xsl:if test="@is-open"><xsl:attribute name="style">display: none;</xsl:attribute></xsl:if>
			<a onclick="showFilter();">Отфильтровать</a>
		</div>

		<xsl:variable name="is-sortable">
			<xsl:choose>
				<xsl:when test="@is-sortable">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="is-date">
			<xsl:choose>
				<xsl:when test="@is-date">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<form id="filter" onsubmit="filterUpdate('filter-content', true, {$is-sortable}, {$is-date}); return false;">
			<xsl:if test="@is-open"><xsl:attribute name="style">display: block;</xsl:attribute></xsl:if>
			<div class="filter-close"><a onclick="hideFilter();">&times;</a></div>
			<xsl:if test="$is-date = 'true'">
                <xsl:call-template name="date-filter" />
            </xsl:if>

			<xsl:if test="@is-title">
				<div class="filter-input">
					<label for="filter-title">Название</label>
					<input type="text" name="filter_title" id="filter-title" class="string" value="{filter-title}" />
				</div>
			</xsl:if>

			<xsl:if test="@is-name">
				<div class="filter-input">
					<label for="filter-name">Имя</label>
					<input type="text" name="filter_name" id="filter-name" class="string" value="{filter-name}" />
				</div>
			</xsl:if>

			<xsl:if test="@is-email">
				<div class="filter-input">
					<label for="filter-email">Электропочта</label>
					<input type="text" name="filter_email" id="filter-email" class="string" value="{filter-email}" />
				</div>
			</xsl:if>

			<xsl:apply-templates select="filter-param[@name and title and item]" />

			<div style="text-align: right;"><input type="submit" value="Выбрать" /></div>
			<input type="hidden" name="filter_selected_id" value="{ancestor::node()[name() = 'module']/@id}" />
		</form>
		<br clear="all" />

		<xsl:if test="@type = 'filter'">
			<ul id="filter-content"><xsl:if test="@is-sortable"><xsl:attribute name="class">sortable</xsl:attribute></xsl:if></ul>
			<script type="text/javascript" language="JavaScript"><xsl:value-of select="concat('filterUpdate(&quot;filter-content&quot;, false, ', $is-sortable, ', ', $is-date, ');')" /></script>
		</xsl:if>
	</xsl:template>

	<xsl:template match="filter-param">
		<div class="filter-input">
			<table class="chooser-item">
				<tr>
					<td><input type="checkbox" name="is_filter_{@name}" id="is-filter-{@name}" value="1" onclick="changeElementVisibility('filter-{@name}-ele', this.checked);">
						<xsl:if test="@is-selected">
                            <xsl:attribute name="checked">true</xsl:attribute>
                        </xsl:if>
					</input></td>
					<td class="chooser-label">
						<label for="is-filter-{@name}" class="filter-name"><xsl:value-of select="title" disable-output-escaping="yes" /></label>
						<table class="chooser-item" id="filter-{@name}-ele">
							<xsl:if test="not(@is-selected)">
                                <xsl:attribute name="style">display: none;</xsl:attribute>
                            </xsl:if>

							<xsl:for-each select="item"><tr>
								<td><input type="checkbox" name="filter_{parent::node()/@name}[]" id="{generate-id()}" value="{@value}">
									<xsl:if test="(parent::node()/@is-selected and @is-selected) or (parent::node()/@is-selected)">
                                        <xsl:attribute name="checked">true</xsl:attribute>
                                    </xsl:if>
								</input></td>
								<td class="chooser-label"><label for="{generate-id()}"><xsl:value-of select="text()" disable-output-escaping="yes" /></label></td>
							</tr></xsl:for-each>
						</table>
					</td>
				</tr>
			</table>
			<br clear="all" />
		</div>
	</xsl:template>

	<xsl:template name="date-filter">
		<table class="date-filter">
			<tr>
				<td class="label">С</td>
				<td class="form-calendar">
					<input type="hidden" name="filter_from" id="filter-from" value="{@from}" />
					<input type="text" id="filter-from-input" onblur="calendarParseInput('filter-from');" />
					<button onclick="calendarSwitcher('filter-from', event); return false;"><img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" /></button>
					<script type="text/javascript" language="JavaScript">calendarInit('filter-from');</script>
				</td>
			</tr>
			<tr>
				<td class="label">По</td>
				<td class="form-calendar">
					<input type="hidden" name="filter_till" id="filter-till" value="{@till}" />
					<input type="text" id="filter-till-input" onblur="calendarParseInput('filter-till');" />
					<button onclick="calendarSwitcher('filter-till', event); return false;"><img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" /></button>
					<script type="text/javascript" language="JavaScript">calendarInit('filter-till');</script>
				</td>
			</tr>
		</table>

		<table class="date-filter-periods" style="margin-bottom: 20px;">
			<tr>
				<td><a onclick="dateFilterFromDate('{@today}')">Сегодня</a></td>
				<td><a onclick="dateFilterFromDate('{@week}')">Неделя</a></td>
				<td><a onclick="dateFilterFromDate('{@month}')">Месяц</a></td>
				<td><a onclick="dateFilterFromDate('{@all-from}', '{@all-till}')">&laquo;Все&raquo;</a></td>
			</tr>
		</table>
	</xsl:template>

	<xsl:template match="local-navigation" name="local-navigation" mode="list">
		<ul id="filter-content">
			<xsl:if test="@is-sortable"><xsl:attribute name="class">sortable</xsl:attribute></xsl:if>
			<xsl:choose>
				<xsl:when test="item">
					<xsl:apply-templates select="item" mode="local-navigation">
						<xsl:with-param name="selected-id" select="ancestor::node()[name() = 'module']/@id" />
						<xsl:with-param name="is-sortable"><xsl:if test="@is-sortable">1</xsl:if></xsl:with-param>
					</xsl:apply-templates>
				</xsl:when>
				<xsl:otherwise>Нет</xsl:otherwise>
			</xsl:choose>
		</ul>

		<xsl:if test="@is-sortable">
			<script type="text/javascript">
			    $(function() {
			        $("#filter-content").sortable({update: itemSort});
			    });
			</script>
		</xsl:if>
	</xsl:template>

	<xsl:template match="item" mode="local-navigation">
		<xsl:param name="selected-id" />
		<xsl:param name="is-sortable">0</xsl:param>

		<li>
			<xsl:if test="$is-sortable = 1"><xsl:attribute name="id">local-item-<xsl:value-of select="position()" /></xsl:attribute></xsl:if>
			<xsl:for-each select="@*[name() = 'xml:lang' or name() = 'prefix']"><xsl:value-of select="concat(., '&nbsp;')" /></xsl:for-each>

			<xsl:choose>
				<xsl:when test="@is-sort-only">
					<span>
						<xsl:attribute name="class">
							<xsl:text>sort-only</xsl:text>
							<xsl:choose>
								<xsl:when test="@id = $selected-id"> selected</xsl:when>
								<xsl:when test="@status"> <xsl:value-of select="@status" /></xsl:when>
								<xsl:when test="not(@is-published)"> hidden</xsl:when>
							</xsl:choose>
						</xsl:attribute>
						<xsl:value-of select="title[last()]" disable-output-escaping="yes" />
					</span>
				</xsl:when>
				<xsl:otherwise>
					<a href="./?id={@id}">
						<xsl:choose>
							<xsl:when test="@id = $selected-id"><xsl:attribute name="class">selected</xsl:attribute></xsl:when>
							<xsl:when test="@status"><xsl:attribute name="class"><xsl:value-of select="@status" /></xsl:attribute></xsl:when>
							<xsl:when test="not(@is-published)"><xsl:attribute name="class">hidden</xsl:attribute></xsl:when>
						</xsl:choose>
						<xsl:value-of select="title[last()]" disable-output-escaping="yes" />
					</a>
				</xsl:otherwise>
			</xsl:choose>

			<xsl:if test="$is-sortable = 1"><input type="hidden" value="{@id}" /></xsl:if>
		</li>
	</xsl:template>

	<xsl:template match="title" mode="subtitle">
		<h1>
            <xsl:value-of select="text()" disable-output-escaping="yes" />
        </h1>
	</xsl:template>

	<xsl:template match="update-status">
		<div class="form-message">
			<xsl:choose>
				<xsl:when test="@type = 'error'">
					<xsl:attribute name="class">form-message error</xsl:attribute>
					<xsl:choose>
						<xsl:when test="text()"><xsl:value-of select="text()" disable-output-escaping="yes" /></xsl:when>
						<xsl:otherwise>Информация не&nbsp;сохранена<br /><nobr>из-за допущенных</nobr> ошибок</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="@type = 'success'">
					<xsl:attribute name="class">form-message success</xsl:attribute>
					<xsl:choose>
						<xsl:when test="text()"><xsl:value-of select="text()" disable-output-escaping="yes" /></xsl:when>
						<xsl:otherwise>Информация сохранена</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
			</xsl:choose>
		</div>
	</xsl:template>

	<xsl:template name="get-page-link">
		<xsl:variable name="url">
			<xsl:call-template name="get-page-url">
				<xsl:with-param name="is-http" select="falses" />
			</xsl:call-template>
		</xsl:variable>

		<a href="http://{$url}"><xsl:value-of select="$url" /></a>
	</xsl:template>


    <!--
    Navigation
    -->

    <xsl:template match="system" mode="navigation">
        <table width="100%">
            <tr>
                <td id="logo">
                    <a href="/">
                        <xsl:value-of select="title" disable-output-escaping="yes" />
                    </a>
                </td>

                <td id="navigation">
                    <xsl:apply-templates select="back-user" mode="navigation" />
                    <xsl:apply-templates select="navigation/back-section" mode="navigation" />
                </td>
            </tr>
        </table>
    </xsl:template>

    <xsl:template match="navigation/back-section" mode="navigation">
        <div class="nav-item">
            <a href="{@uri}">
                <xsl:value-of select="title" disable-output-escaping="yes" />
            </a>
        </div>
    </xsl:template>

    <xsl:template match="navigation/back-section[
        starts-with(/node()/url, @uri) and
        (@uri != '/cms/' or /node()/url != '/cms/')
    ]" mode="navigation">
        <div class="nav-item selected">
            <a href="{@uri}">
                <xsl:value-of select="title" disable-output-escaping="yes" />
            </a>
        </div>
    </xsl:template>

    <xsl:template match="navigation/back-section[@uri = /node()/url]" mode="navigation">
        <div class="nav-item selected">
            <xsl:value-of select="title" disable-output-escaping="yes" />
        </div>
    </xsl:template>

    <xsl:template match="back-user" mode="navigation">
        <div id="user-info">
            <!-- <xsl:value-of select="title" /><br /> -->
            <xsl:apply-templates select="../session/workmates[back-user]" />

            <a href="./?e">Выйти</a>
        </div>
    </xsl:template>

    <xsl:template match="workmates">
        <div class="workmate-warning">
            <xsl:choose>
                <xsl:when test="count(back-user) = 1">
                    <xsl:text>Вместе с&nbsp;вами работает</xsl:text><br />
                    <xsl:text>пользователь </xsl:text>
                    <xsl:value-of select="back-user" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>Вместе с&nbsp;вами работают</xsl:text><br />
                    <xsl:text>другие пользователи (</xsl:text>

                    <xsl:for-each select="back-user">
                        <xsl:value-of select="text()" />
                        <xsl:if test="position() != last()">, </xsl:if>
                    </xsl:for-each>

                    <xsl:text>)</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:text>.</xsl:text>
        </div>
    </xsl:template>


    <!--
    Footer
    -->
    <xsl:template name="page-footer">
        <div id="footer">
            <a href="http://sitedev.ru">Система управления сайтом</a>

            <xsl:text> с 2007 &bull; </xsl:text>

            <a href="mailto:support@sitedev.ru">support@sitedev.ru</a>
        </div>
    </xsl:template>
</xsl:stylesheet>
