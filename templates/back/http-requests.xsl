<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" indent="no" encoding="utf-8" />

	<xsl:include href="../common.xsl" />
	<xsl:include href="common.xsl" />
	<xsl:include href="forms.xsl" />

	<xsl:template match="http-request[@type = 'tree_multiple' or @type = 'tree_single' or @type = 'tree_list'][not(content) or not(content/item)]">
		<span style="font-size: 1.2em;">Нет</span>
	</xsl:template>

	<xsl:template match="http-request[@type = 'tree_multiple' or @type = 'tree_single'][content/item]">
		<xsl:apply-templates select="content/item" mode="form-tree" />
	</xsl:template>

	<xsl:template match="http-request[@type = 'tree_list'][content/item]">
		<xsl:apply-templates select="content/item" mode="list-tree" />
	</xsl:template>

	<xsl:template match="item" mode="form-tree">
		<xsl:variable name="module-name" select="/node()/@module-name" />
		<xsl:variable name="field-name" select="/node()/@field-name" />

		<xsl:variable name="type"><xsl:choose>
			<xsl:when test="/node()/@type = 'tree_multiple'">multiple</xsl:when>
			<xsl:otherwise>single</xsl:otherwise>
		</xsl:choose></xsl:variable>

		<table class="tree">
			<tr>
				<td class="tree-icon"><xsl:choose>
					<xsl:when test="item or @has-children">
						<a onclick="treeCollapse(this, '{$module-name}', '{$field-name}', '{@id}', '{$type}'); return false;"><img width="9" height="9" alt=""><xsl:attribute name="src"><xsl:choose>
							<xsl:when test="item">/cms/f/icon-minus.gif</xsl:when>
							<xsl:otherwise>/cms/f/icon-plus.gif</xsl:otherwise>
						</xsl:choose></xsl:attribute></img></a>
					</xsl:when>
					<xsl:otherwise><img src="/cms/f/icon-bullet.gif" width="9" height="9" alt="" /></xsl:otherwise>
				</xsl:choose></td>

				<td class="tree-input"><input type="checkbox" id="{generate-id()}" value="{@id}">
					<xsl:attribute name="name">
						<xsl:value-of select="$field-name" />
						<xsl:if test="/node()/@type = 'tree_multiple'">[]</xsl:if>
					</xsl:attribute>
					<xsl:attribute name="type"><xsl:choose>
						<xsl:when test="/node()/@type = 'tree_multiple'">checkbox</xsl:when>
						<xsl:otherwise>radio</xsl:otherwise>
					</xsl:choose></xsl:attribute>
					<xsl:if test="/node()/content/selected/text() = @id"><xsl:attribute name="checked">1</xsl:attribute></xsl:if>
				</input></td>

				<td class="tree-title">
					<xsl:for-each select="@*[name() = 'xml:lang' or name() = 'prefix']"><xsl:value-of select="concat(., '&nbsp;')" /></xsl:for-each>
					<label for="{generate-id()}">
						<xsl:if test="not(@is-published)"><xsl:attribute name="class">hidden</xsl:attribute></xsl:if>
						<xsl:value-of select="title/text()" disable-output-escaping="yes" />
					</label>
				</td>
			</tr>
		</table>

		<div id="{$field-name}-{@id}" class="tree-subitems"><xsl:if test="item">
			<xsl:attribute name="style">display: block;</xsl:attribute>
			<xsl:apply-templates select="item" mode="form-tree" />
		</xsl:if></div>
	</xsl:template>

	<xsl:template match="item" mode="list-tree">
		<xsl:variable name="module-name" select="/node()/@module-name" />
		<xsl:variable name="field-name" select="/node()/@field-name" />

		<div id="sort-item-{@id}" class="sort-item">
			<input type="hidden" id="sort-item-{@id}-id" value="{@id}" />

			<table class="tree">
				<tr>
					<td class="tree-icon"><xsl:choose>
						<xsl:when test="item or @has-children">
							<a onclick="treeCollapse(this, '{$module-name}', '{$field-name}', '{@id}', 'list'); return false;"><img width="9" height="9" alt=""><xsl:attribute name="src"><xsl:choose>
								<xsl:when test="item">/cms/f/icon-minus.gif</xsl:when>
								<xsl:otherwise>/cms/f/icon-plus.gif</xsl:otherwise>
							</xsl:choose></xsl:attribute></img></a>
						</xsl:when>
						<xsl:otherwise><img src="/cms/f/icon-bullet.gif" width="9" height="9" alt="" /></xsl:otherwise>
					</xsl:choose></td>

					<td class="tree-title">
						<xsl:for-each select="@*[name() = 'xml:lang' or name() = 'prefix']"><xsl:value-of select="concat(., '&nbsp;')" /></xsl:for-each>
						<a href="?id={@id}">
							<xsl:choose>
								<xsl:when test="/node()/content/selected/text() = @id"><xsl:attribute name="class">selected</xsl:attribute></xsl:when>
								<xsl:when test="not(@is-published)"><xsl:attribute name="class">hidden</xsl:attribute></xsl:when>
							</xsl:choose>
							<xsl:value-of select="title/text()" disable-output-escaping="yes" />
						</a>
					</td>
				</tr>
			</table>

			<div id="{$field-name}-{@id}" class="tree-subitems"><xsl:if test="item">
				<xsl:attribute name="style">display: block;</xsl:attribute>
				<xsl:apply-templates select="item" mode="list-tree" />
			</xsl:if></div>
		</div>
	</xsl:template>

	<xsl:template match="http-request[@type = 'document-data']">
		<xsl:apply-templates select="content/document-data" />
	</xsl:template>

	<xsl:template match="document-data">
		<div class="document-data" id="document-data-ele-{@id}">
			<input type="hidden" value="{@id}" />
			<xsl:apply-templates select="self::node()" mode="document-data" />
			<br clear="all" />
		</div>
	</xsl:template>

	<xsl:template match="document-data[not(@is-mount)]" mode="document-data">
		<a onclick="openWindow('data.php?parent_id={/node()/@parent-id}&amp;id={@id}')">
			<xsl:attribute name="class">
				<xsl:text>new-window-link</xsl:text>
				<xsl:if test="not(@is-published)"> hidden</xsl:if>
			</xsl:attribute>
			<xsl:value-of select="title/text()" disable-output-escaping="yes" />
		</a>
	</xsl:template>

	<xsl:template match="document-data[@is-mount]" mode="document-data">
		<div style="width: 30%; float: left; margin-bottom: 5px; min-width: 100px; max-width: 250px;"><div style="padding-right: 20px;">
			<label for="{generate-id()}">
				<a onclick="openWindow('data.php?parent_id={/node()/@parent-id}&amp;id={@id}')">
					<xsl:attribute name="class">
						<xsl:text>new-window-link</xsl:text>
						<xsl:if test="not(@is-published)"> hidden</xsl:if>
					</xsl:attribute>
					<xsl:value-of select="title/text()" disable-output-escaping="yes" />
				</a>
			</label>
			<xsl:apply-templates select="@tag|controller|auth-group" mode="document-data" />
		</div></div>
		<div style="width: 70%; float: left;">
			<xsl:apply-templates select="self::node()" mode="document-data-form-ele" />
		</div>
	</xsl:template>

	<xsl:template match="@tag|controller|auth-group" mode="document-data">
		<div style="font-size: 0.84em; clear: both;"><xsl:value-of select=".|text()" disable-output-escaping="yes" /></div>
	</xsl:template>

	<xsl:template match="document-data[@type-id = 'string']" mode="document-data-form-ele">
		<input type="text" id="{generate-id()}" name="document_data_form_ele_{@id}" value="{content/text()}" style="width: 70%;" />
	</xsl:template>

	<xsl:template match="document-data[@type-id = 'integer' or @type-id = 'float']" mode="document-data-form-ele">
		<input type="text" id="{generate-id()}" name="document_data_form_ele_{@id}" value="{content/text()}" style="width: 30%; text-align: right;" />
	</xsl:template>

	<xsl:template match="document-data[@type-id = 'image']" mode="document-data-form-ele">
		<xsl:choose>
			<xsl:when test="additional/*/image">
				<table class="chooser-item"><tr>
					<td><input type="radio" name="document_data_form_ele_{@id}" value="" id="{generate-id()}">
						<xsl:if test="not(content/text())"><xsl:attribute name="checked">true</xsl:attribute></xsl:if>
					</input></td>
					<td class="chooser-label"><label for="{generate-id()}">нет</label></td>
				</tr></table>
				<xsl:apply-templates select="additional/self/image" mode="document-image" />

				<xsl:if test="additional/others">
					<div>
						<xsl:attribute name="style">
							<xsl:text>clear: both; padding-bottom: 1em;</xsl:text>
							<xsl:if test="additional/self"> padding-top: 1em;</xsl:if>
						</xsl:attribute>
						<a class="function" onclick="changeElementVisibility('other-images-{@id}');">Все изображения</a>
						<div id="other-images-{@id}">
							<xsl:attribute name="style">
								<xsl:text>margin-top: 1em; display: </xsl:text>
								<xsl:choose>
									<xsl:when test="additional/others/image/@uri = content/text()">block</xsl:when>
									<xsl:otherwise>none</xsl:otherwise>
								</xsl:choose>
								<xsl:text>;</xsl:text>
							</xsl:attribute>
							<xsl:apply-templates select="additional/others/image" mode="document-image" />
						</div>
					</div>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>Изображения не&nbsp;загружены.</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="image" mode="document-image">
		<xsl:variable name="name" select="concat('document_data_form_ele_', ancestor::node()[name() = 'document-data']/@id)" />
		<xsl:variable name="value" select="ancestor::node()[name() = 'document-data']/content/text()" />

		<table class="chooser-item"><tr>
			<td><input type="radio" name="{$name}" value="{@uri}" id="{generate-id()}">
				<xsl:if test="@uri = $value"><xsl:attribute name="checked">true</xsl:attribute></xsl:if>
			</input></td>
			<td class="chooser-label"><label for="{generate-id()}" title="{@uri}">
				<a href="{@uri}"><xsl:value-of select="@filename" /></a><br />
				<span style="font-size: 0.84em;"><xsl:value-of select="concat(@width, '&times;', @height)" /></span>
			</label></td>
		</tr></table>
	</xsl:template>

	<xsl:template match="document-data" mode="document-data-form-ele">
		<textarea id="{generate-id()}" name="document_data_form_ele_{@id}" class="text" style="width: 100%;">
			<xsl:value-of select="content/text()" />
		</textarea>
	</xsl:template>

	<xsl:template match="http-request[@type = 'filter']">
		<xsl:variable name="is-sortable"><xsl:if test="@is-sortable">1</xsl:if></xsl:variable>

		<xsl:choose>
			<xsl:when test="content[item]">
				<xsl:apply-templates select="content/item" mode="local-navigation">
					<xsl:with-param name="selected-id" select="@selected-id" />
					<xsl:with-param name="is-sortable" select="$is-sortable" />
				</xsl:apply-templates>

				<xsl:for-each select="content/list-navigation">
					<xsl:call-template name="list-navigation">
						<xsl:with-param name="total" select="@total" />
						<xsl:with-param name="per-page">
						    <xsl:value-of select="@per-page" />
                        </xsl:with-param>
						<xsl:with-param name="page" select="@page" />
						<xsl:with-param name="type">query</xsl:with-param>
						<xsl:with-param name="separator">&middot;</xsl:with-param>
						<xsl:with-param name="url-subquery" select="/node()/url/@query" />
						<xsl:with-param name="is-tiny">1</xsl:with-param>
						<xsl:with-param name="step">5</xsl:with-param>
						<xsl:with-param name="js-func">
							<xsl:choose>
								<xsl:when test="$is-sortable = 1">filterUpdateNav(~, true)</xsl:when>
								<xsl:otherwise>filterUpdateNav</xsl:otherwise>
							</xsl:choose>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>Нет</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="http-request[@type = 'back-logs']">
		<xsl:variable name="is-sortable"><xsl:if test="@is-sortable">1</xsl:if></xsl:variable>

		<xsl:choose>
			<xsl:when test="content[item]">
				<table class="back-logs">
					<xsl:for-each select="content/item">
						<tr>
							<td class="date">
								<xsl:value-of select="@date" />

								<xsl:for-each select="action">
									<div>
										<xsl:if test="parent::node()/@action-id">
											<xsl:attribute name="class">action-<xsl:value-of select="parent::node()/@action-id" /></xsl:attribute>
										</xsl:if>
										<xsl:value-of select="text()" disable-output-escaping="yes" />
									</div>
								</xsl:for-each>
							</td>
							<td>
								<xsl:variable name="is-subsection">
									<xsl:choose>
										<xsl:when test="@script-name or @entry-id or description[string-length(text()) &gt; 50]">true</xsl:when>
										<xsl:otherwise>false</xsl:otherwise>
									</xsl:choose>
								</xsl:variable>

								<xsl:choose>
									<xsl:when test="$is-subsection = 'true'">
										<a onclick="changeElementVisibility('back-log-section-{generate-id()}');" class="section">
											<xsl:choose>
												<xsl:when test="section"><xsl:value-of select="section/text()" disable-output-escaping="yes" /></xsl:when>
												<xsl:otherwise><i>Без раздела</i></xsl:otherwise>
											</xsl:choose>
										</a>
									</xsl:when>
									<xsl:otherwise>
										<xsl:choose>
											<xsl:when test="section"><xsl:value-of select="section/text()" disable-output-escaping="yes" /></xsl:when>
											<xsl:otherwise><i>Без раздела</i></xsl:otherwise>
										</xsl:choose>
									</xsl:otherwise>
								</xsl:choose>

								<xsl:choose>
									<xsl:when test="description[string-length(text()) &lt;= 50]">
										<xsl:text>. </xsl:text>
										<xsl:value-of select="description/text()" disable-output-escaping="yes" /><br />
									</xsl:when>
									<xsl:when test="description">&hellip;<br /></xsl:when>
								</xsl:choose>

								<xsl:if test="$is-subsection = 'true'">
									<div class="section" id="back-log-section-{generate-id()}">
										<xsl:for-each select="@script-name">
											<xsl:value-of select="concat('Скрипт: ', .)" /><br />
										</xsl:for-each>
										<xsl:for-each select="@entry-id">
											<xsl:value-of select="concat('ID записи: ', .)" /><br />
										</xsl:for-each>
										<xsl:for-each select="description[string-length(text()) &gt; 50]">
											<xsl:value-of select="text()" disable-output-escaping="yes" /><br />
										</xsl:for-each>
									</div>
								</xsl:if>

								<div>
									<a onclick="changeElementVisibility('back-log-user-{generate-id()}');" class="section"><xsl:value-of select="user/text()" disable-output-escaping="yes" /></a>
									<div class="section" id="back-log-user-{generate-id()}">
										<xsl:value-of select="concat('IP: ', @user-ip)" /><br />
										<xsl:value-of select="concat('Браузер: ', user-agent/text())" disable-output-escaping="yes" />
									</div>
								</div>
							</td>
						</tr>
					</xsl:for-each>
				</table>

				<xsl:for-each select="content/list-navigation">
					<xsl:call-template name="list-navigation">
						<xsl:with-param name="total" select="@total" />
						<xsl:with-param name="per-page" select="@per-page" />
						<xsl:with-param name="page" select="@page" />
						<xsl:with-param name="type">query</xsl:with-param>
						<xsl:with-param name="separator">&middot;</xsl:with-param>
						<xsl:with-param name="url-subquery" select="/node()/url/@query" />
						<xsl:with-param name="is-tiny">0</xsl:with-param>
						<xsl:with-param name="step">10</xsl:with-param>
						<xsl:with-param name="js-func">
							<xsl:choose>
								<xsl:when test="$is-sortable = 1">filterUpdateNav(~, true)</xsl:when>
								<xsl:otherwise>filterUpdateNav</xsl:otherwise>
							</xsl:choose>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>Нет</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

    <!--
    Список элементов из таблицы с тройным
    составным первичным ключом
    -->
    <xsl:template match="http-request[@type = 'triple-link']">
        <xsl:call-template name="triple-link-item" />
    </xsl:template>
</xsl:stylesheet>
