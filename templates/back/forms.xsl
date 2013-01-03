<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="form">
		<xsl:if test="group and count(group) > 1">
			<script type="text/javascript">
				<xsl:text>var formGroups = new Array(</xsl:text>
				<xsl:for-each select="group[@name]">
					<xsl:value-of select="concat('&quot;', @name, '&quot;')" />
					<xsl:if test="position() != last()">, </xsl:if>
				</xsl:for-each>
				<xsl:text>);</xsl:text>
			</script>

			<xsl:call-template name="form-group-tabs" />
		</xsl:if>

		<form method="post" enctype="multipart/form-data">
			<xsl:for-each select="ancestor::node()[name() = 'module' and @id != '']">
				<input type="hidden" name="current_object_id" id="current-object-id" value="{@id}" />
			</xsl:for-each>

			<xsl:choose>
				<xsl:when test="count(group) > 1">
					<xsl:apply-templates select="group" />
				</xsl:when>
				<xsl:when test="group">
					<table class="form">
						<xsl:apply-templates select="group/element" mode="form" />
						<xsl:call-template name="buttons" />
					</table>
				</xsl:when>
				<xsl:otherwise>
					<table class="form">
						<xsl:apply-templates select="element" mode="form" />
						<xsl:call-template name="buttons" />
					</table>
				</xsl:otherwise>
			</xsl:choose>
		</form>

        <xsl:if test="//element[contains(@type, 'text')]">
            <script type="text/javascript">replaceTextareaCdata();</script>
        </xsl:if>
	</xsl:template>

	<xsl:template name="form-group-tabs">
		<table class="form-group-tabs">
			<xsl:for-each select="group[@name and title/text()]">
				<td id="form-group-{@name}-tab">
					<xsl:if test="position() = 1 or @is-selected">
						<xsl:attribute name="class">
							<xsl:if test="position() = 1">first</xsl:if>
							<xsl:if test="@is-selected or (position() = 1 and not(parent::node()/group[@name and title/text() and @is-selected]))">
								<xsl:if test="position() = 1"><xsl:text> </xsl:text></xsl:if>
								<xsl:text>selected</xsl:text>
							</xsl:if>
						</xsl:attribute>
					</xsl:if>

					<a onclick="showFormGroup('{@name}'); return false;">
                        <xsl:value-of select="title/text()" disable-output-escaping="yes" />
                    </a>
				</td>
			</xsl:for-each>
		</table>
	</xsl:template>

	<xsl:template match="group">
		<div id="form-group-{@name}">
			<xsl:if test="not(@is-selected or (position() = 1 and not(parent::node()/group[@name and title/text() and @is-selected])))">
				<xsl:attribute name="style">display: none;</xsl:attribute>
			</xsl:if>

			<table class="form">
				<xsl:apply-templates select="element" mode="form" />
				<xsl:apply-templates select="additional" mode="group" />
				<xsl:call-template name="buttons" />
			</table>
		</div>
	</xsl:template>

	<xsl:template match="additional" mode="group">
		<tr>
			<td>
				<xsl:if test="(group and count(group/element) > 1) or (not(group) and count(element) > 1)">
					<xsl:attribute name="colspan">2</xsl:attribute>
				</xsl:if>

				<xsl:apply-templates />
			</td>
		</tr>
	</xsl:template>

	<xsl:template name="buttons">
		<tr>
			<td>
				<xsl:if test="
				    (group and count(group/element) > 1) or
				    (not(group) and count(element) >= 1)
				">
					<xsl:attribute name="colspan">2</xsl:attribute>
				</xsl:if>

				<div class="buttons">
					<xsl:choose>
						<xsl:when test="button">
							<xsl:apply-templates select="button" />
						</xsl:when>
						<xsl:when test="parent::node()/button">
							<xsl:apply-templates select="parent::node()/button" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="button" />
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="button" name="button">
		<input type="submit">
			<xsl:attribute name="name">
				<xsl:choose>
					<xsl:when test="@name"><xsl:value-of select="@name" /></xsl:when>
					<xsl:otherwise>submit</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>

			<xsl:attribute name="value">
                <xsl:value-of select="text()" disable-output-escaping="yes" />

				<xsl:choose>
					<xsl:when test="@name = 'delete'">&hellip;</xsl:when>
					<xsl:when test="@name = 'close_window'"> &times;</xsl:when>
				</xsl:choose>
			</xsl:attribute>

			<xsl:choose>
				<xsl:when test="@name = 'delete'">
					<xsl:attribute name="onclick">
						<xsl:text>return confirm('Вы уверены?')</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@name = 'close_window'">
					<xsl:attribute name="onclick">
						<xsl:text>window.close(); return false</xsl:text>
					</xsl:attribute>
				</xsl:when>
			</xsl:choose>
		</input>
		<xsl:if test="position() != last()">
			<xsl:text> </xsl:text>
		</xsl:if>
	</xsl:template>

	<xsl:template match="element" mode="form">
		<tr>
			<xsl:if test="count(parent::node()/element) > 1 or (
			              not(ancestor::node()[2][group]) and
			              count(parent::node()/element) = 1)">
				<td class="label">
                    <xsl:choose>
                        <xsl:when test="@is-readonly">
                            <xsl:attribute name="style">padding-top: 0;</xsl:attribute>
                            <xsl:value-of select="label" disable-output-escaping="yes" />
                        </xsl:when>
                        <xsl:otherwise>
                            <label>
                                <xsl:attribute name="for">
                                    <xsl:choose>
                                        <xsl:when test="@type = 'calendar'">
                                            <xsl:value-of select="@name" />
                                            <xsl:text>-input</xsl:text>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:text>form-ele-</xsl:text>
                                            <xsl:value-of select="@name" />
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:attribute>

                                <xsl:value-of select="label" disable-output-escaping="yes" />
                                <xsl:if test="@is-required"><sup class="required">&bull;</sup></xsl:if>
                            </label>
                        </xsl:otherwise>
                    </xsl:choose>

					<xsl:if test="description/text() and (@type = 'image' or @type = 'text' or @type = 'large_text' or string-length(description/text()) &lt;= 50) and not(@type = 'adding_files')">
						<div class="description"><xsl:value-of select="description/text()" disable-output-escaping="yes" /></div>
					</xsl:if>
				</td>
			</xsl:if>
			<td>
			    <xsl:attribute name="class">
			        <xsl:text>input</xsl:text>
			        <xsl:if test="count(parent::node()/element) = 1"> alone</xsl:if>
			    </xsl:attribute>

				<xsl:call-template name="form-element" />

                <!--
                Прежняя реализация
                @todo Убрать после перехода на новую реализацию.
                -->
				<xsl:if test="@update-type != '' and
				              @update-type != 'no_update' and
				              @update-type != 'success'">

					<div class="field-error-message">
						<xsl:choose>
							<xsl:when test="error-message">
							    <xsl:value-of select="error-message"
							                  disable-output-escaping="yes" />
							</xsl:when>
							<xsl:when test="@update-type = 'error_required'">Поле обязательно для&nbsp;заполнения.</xsl:when>
							<xsl:when test="@update-type = 'error_spelling'">Некорректное значение.</xsl:when>
							<xsl:when test="@update-type = 'error_exist'">Значение уже&nbsp;используется.</xsl:when>
							<xsl:otherwise>Некорректное значение.</xsl:otherwise>
						</xsl:choose>
					</div>
				</xsl:if>

                <xsl:if test="
                    @update-status != 'success' and
                    @update-status != 'no-update'
                ">
                    <div class="field-error-message">
                        <xsl:choose>
                            <xsl:when test="status-error-message">
                                <xsl:value-of select="status-error-message"
                                              disable-output-escaping="yes" />
                            </xsl:when>
                            <xsl:when test="@update-type = 'error-required'">Поле обязательно для&nbsp;заполнения.</xsl:when>
                            <!-- <xsl:when test="@update-type = 'error-spelling'">Некорректное значение.</xsl:when> -->
                            <xsl:when test="@update-type = 'error-exist'">Значение уже&nbsp;используется.</xsl:when>
                            <xsl:otherwise>Некорректное значение.</xsl:otherwise>
                        </xsl:choose>
                    </div>
                </xsl:if>

				<xsl:if test="
                    description and
                    @type != 'image' and
                    @type != 'text' and
                    @type != 'large_text'
                    and string-length(description/text()) > 50 and
                    not(@type = 'adding_files')
                ">
					<div class="description">
						<xsl:value-of select="description"
                                      disable-output-escaping="yes" />
					</div>
				</xsl:if>
			</td>
		</tr>
	</xsl:template>

	<xsl:template name="form-element">
		<xsl:choose>
            <xsl:when test="@type = 'boolean' and @is-readonly and value[text() = '1']">
                <div class="form-float-ele" title="Да">&bull;</div>
            </xsl:when>

            <xsl:when test="@type = 'boolean' and @is-readonly">
                <div class="form-float-ele">Нет</div>
            </xsl:when>

			<xsl:when test="@type = 'boolean'">
				<div class="form-float-ele">
					<input type="checkbox" name="{@name}" id="form-ele-{@name}" value="1">
						<xsl:if test="value/text() and value/text() != '0'">
							<xsl:attribute name="checked">true</xsl:attribute>
						</xsl:if>
					</input>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'email' or @type = 'string' or @type = 'folder' or @type = 'filename' or @type = 'word' or @type = 'uri' or @type = 'login'">
                <div class="form-float-ele">
                    <xsl:choose>
                        <xsl:when test="@is-readonly">
                            <xsl:value-of select="value" />
                        </xsl:when>
                        <xsl:otherwise>
                            <input name="{@name}" id="form-ele-{@name}" type="text" maxlength="255" class="{@type}">
                                <xsl:attribute name="value">
                                    <xsl:choose>
                                        <xsl:when test="error/value/text()"><xsl:value-of select="error/value/text()" /></xsl:when>
                                        <xsl:otherwise><xsl:value-of select="value/text()" /></xsl:otherwise>
                                    </xsl:choose>
                                </xsl:attribute>
                            </input>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
			</xsl:when>

			<xsl:when test="@type = 'name'">
				<table class="form-name form-float-ele">
					<tr>
						<td class="last-name">
							<input type="text" name="{@name}_last_name" id="form-ele-{@name}" maxlength="255">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/last-name"><xsl:value-of select="error/value/last-name" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/last-name" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="first-name">
							<input type="text" name="{@name}_first_name" id="form-ele-{@name}-first-name" maxlength="255">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/first-name"><xsl:value-of select="error/value/first-name" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/first-name" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="patronymic-name">
							<input type="text" name="{@name}_patronymic_name" id="form-ele-{@name}-patronymic-name" maxlength="255">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/patronymic-name"><xsl:value-of select="error/value/patronymic-name" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/patronymic-name" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<xsl:if test="not(preceding::element[@type = 'name'])">
						<tr>
							<td>
								<label for="form-ele-{@name}">
									<xsl:text>Фамилия</xsl:text>
									<xsl:if test="@is-required">
										<sup class="required">&bull;</sup>
									</xsl:if>
								</label>
							</td>
							<td class="first-name">
								<label for="form-ele-{@name}-first-name">
									<xsl:text>Имя</xsl:text>
									<xsl:if test="@is-required">
										<sup class="required">&bull;</sup>
									</xsl:if>
								</label>
							</td>
							<td><label for="form-ele-{@name}-patronymic-name">Отчество</label></td>
						</tr>
					</xsl:if>
				</table>
			</xsl:when>

			<xsl:when test="@type = 'document_parent_id'">
				<input type="hidden" name="{@name}" id="form-ele-{@name}" value="{value/text()}" />

				<div id="{@name}-change">
					<xsl:if test="not(ancestor::node()[name() = 'module']/@id)"><xsl:attribute name="style">display: none;</xsl:attribute></xsl:if>
					<!--xsl:attribute name="style">
						<xsl:text>display: </xsl:text>
						<xsl:choose>
							<xsl:when test="ancestor::node()[name() = 'module']/@id">block;</xsl:when>
							<xsl:otherwise>none;</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute-->
					<a onclick="documentParentChooser('{@name}')" class="parent-document-change">Выбрать</a>
				</div>

				<div id="{@name}-chooser">
					<xsl:if test="ancestor::node()[name() = 'module']/@id"><xsl:attribute name="style">display: none;</xsl:attribute></xsl:if>
					<!--xsl:attribute name="style">
						<xsl:text>display: </xsl:text>
						<xsl:choose>
							<xsl:when test="ancestor::node()[name() = 'module']/@id">none;</xsl:when>
							<xsl:otherwise>block;</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute-->
					<a onclick="documentParentChooser('{@name}')" class="parent-document-change">Скрыть</a>
					<div id="{@name}-tree" class="object-tree" />
					<script type="text/javascript">
						<xsl:text>documentParentUpdateBranch('</xsl:text>
						<xsl:value-of select="@name" />
						<xsl:text>-tree', '</xsl:text>
						<xsl:value-of select="@name" />
						<xsl:text>' , '', '</xsl:text>
						<xsl:value-of select="ancestor::node()[name() = 'module']/@id" />
						<xsl:text>', '</xsl:text>
						<xsl:value-of select="value/text()" />
						<xsl:text>');</xsl:text>
					</script>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'multiple_tree' or @type = 'single_tree'">
				<xsl:variable name="module-name" select="ancestor::node()[name() = 'module']/@name" />
				<xsl:variable name="field-name" select="@name" />

				<div id="{$field-name}-tree-open-btn">
					<xsl:if test="not(ancestor::node()[name() = 'module']/@id)"><xsl:attribute name="style">display: none;</xsl:attribute></xsl:if>
					<a onclick="treeSwitcher('{$field-name}')" class="tree-switcher">Выбрать</a>
				</div>

				<div id="{$field-name}-tree-container">
					<xsl:if test="ancestor::node()[name() = 'module']/@id"><xsl:attribute name="style">display: none;</xsl:attribute></xsl:if>
					<a onclick="treeSwitcher('{$field-name}')" class="tree-switcher">Скрыть</a>
					<div id="{$field-name}-tree" class="tree" />
				</div>

				<xsl:variable name="type"><xsl:choose>
					<xsl:when test="@type = 'single_tree'">single</xsl:when>
					<xsl:otherwise>multiple</xsl:otherwise>
				</xsl:choose></xsl:variable>

				<script type="text/javascript">
					<xsl:value-of select="concat('var formTreeValues_', $field-name, ' = new Array(')" />
					<xsl:choose>
						<xsl:when test="@type = 'single_tree' and value/text()"><xsl:value-of select="concat('&quot;', value/text(), '&quot;')" /></xsl:when>
						<xsl:when test="@type = 'single_tree' and not(value/item)">""</xsl:when>
						<xsl:when test="value/item"><xsl:for-each select="value/item">
							<xsl:value-of select="concat('&quot;', text(), '&quot;')" />
							<xsl:if test="position() != last()">, </xsl:if>
						</xsl:for-each></xsl:when>
					</xsl:choose>
					<xsl:value-of select="concat('); treeLoad(&quot;', $field-name, '-tree&quot;, &quot;', $module-name, '&quot;, &quot;', $field-name, '&quot;, &quot;&quot;, &quot;', $type, '&quot;);')" />
				</script>
			</xsl:when>

			<xsl:when test="@type = 'chooser' or @type = 'select' or @type = 'radio'">
				<xsl:variable name="value">
					<xsl:choose>
						<xsl:when test="error/value/text()"><xsl:value-of select="error/value/text()" /></xsl:when>
						<xsl:otherwise><xsl:value-of select="value/text()" /></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<div class="form-float-ele">
					<xsl:choose>
						<xsl:when test="options/group/item">
							<select name="{@name}" id="form-ele-{@name}" class="simple">
								<xsl:for-each select="options/group[item]">
									<optgroup label="{title/text()}">
										<xsl:for-each select="item">
											<option value="{@value}">
												<xsl:if test="$value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
												<xsl:value-of select="text()" disable-output-escaping="yes" />
											</option>
										</xsl:for-each>
									</optgroup>
								</xsl:for-each>
							</select>
						</xsl:when>
						<xsl:when test="count(options/item) = 1">
							<input type="hidden" name="{@name}" value="{options/item/@value}" />
							<xsl:if test="$value = options/item/@value">&bull; </xsl:if>
							<xsl:value-of select="options/item/text()" disable-output-escaping="yes" />
						</xsl:when>
						<xsl:when test="
                            @type = 'select' or
                            (count(options/item) > 3 and @type != 'radio')
                        ">
							<select name="{@name}" id="form-ele-{@name}" class="simple">
								<xsl:for-each select="options/item">
									<option value="{@value}">
										<xsl:if test="$value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
										<xsl:value-of select="text()" disable-output-escaping="yes" />
									</option>
								</xsl:for-each>
							</select>
						</xsl:when>
						<xsl:when test="count(options/item) > 0">
							<xsl:for-each select="options/item">
								<table class="chooser-item">
									<tr>
										<td>
											<input type="radio" name="{ancestor::node()[2]/@name}" id="{generate-id()}" value="{@value}">
												<xsl:if test="@value = $value or (position() = 1 and $value = '')">
													<xsl:attribute name="checked">true</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td class="chooser-label"><label for="{generate-id()}"><xsl:value-of select="text()" disable-output-escaping="yes" /></label></td>
									</tr>
								</table>
							</xsl:for-each>
						</xsl:when>
						<xsl:otherwise>Нет</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'multiple'">
				<div class="form-float-ele">
					<xsl:choose>
						<!-- В четыре колонки -->
						<xsl:when test="count(options/item) > 25">
							<table width="100%">
								<col width="25%" />
								<xsl:for-each select="options/item[position() mod 4 = 1]">
									<tr>
										<td class="multiple multiple-small multiple-first"><xsl:apply-templates select="self::node()" mode="checkbox" /></td>
										<td class="multiple multiple-small"><xsl:apply-templates select="following-sibling::node()[name() = 'item'][1]" mode="checkbox" /></td>
										<td class="multiple multiple-small"><xsl:apply-templates select="following-sibling::node()[name() = 'item'][2]" mode="checkbox" /></td>
										<td class="multiple multiple-small"><xsl:apply-templates select="following-sibling::node()[name() = 'item'][3]" mode="checkbox" /></td>
									</tr>
								</xsl:for-each>
							</table>
						</xsl:when>
						<!-- В две колонки -->
						<xsl:when test="count(options/item) > 5">
							<table>
								<xsl:for-each select="options/item[position() mod 2 = 1]">
									<tr>
										<td class="multiple multiple-first"><xsl:apply-templates select="self::node()" mode="checkbox" /></td>
										<td class="multiple"><xsl:apply-templates select="following-sibling::node()[name() = 'item'][1]" mode="checkbox" /></td>
									</tr>
								</xsl:for-each>
							</table>
						</xsl:when>
						<!-- Столбиком -->
						<xsl:when test="count(options/item) > 3">
							<table class="chooser-item">
								<xsl:apply-templates select="options/item" mode="checkbox">
									<xsl:with-param name="without-table">true</xsl:with-param>
								</xsl:apply-templates>
							</table>
						</xsl:when>
						<!-- Строкой -->
						<xsl:when test="count(options/item) > 0">
							<xsl:apply-templates select="options/item" mode="checkbox" />
						</xsl:when>
						<!-- Нет -->
						<xsl:otherwise>
							<xsl:text>&mdash;</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'text' or @type = 'short_text' or @type = 'large_text'">
				<div class="form-float-ele">
					<textarea name="{@name}" id="form-ele-{@name}">
						<xsl:attribute name="class">
							<xsl:value-of select="@type" />
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="error/value/text()"><xsl:value-of select="error/value/text()" disable-output-escaping="no" /></xsl:when>
							<xsl:otherwise><xsl:value-of select="value/text()" disable-output-escaping="no" /></xsl:otherwise>
						</xsl:choose>
					</textarea>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'date'">
				<table class="form-date form-float-ele">
					<tr>
						<td class="day">
							<input type="text" name="{@name}_day" id="{@name}" maxlength="2">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/day/text()"><xsl:value-of select="error/value/day/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/day/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="date-separator">.</td>
						<td class="month">
							<input type="text" name="{@name}_month" id="{@name}-month" maxlength="2">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/month/text()"><xsl:value-of select="error/value/month/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/month/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="date-separator">.</td>
						<td class="year">
							<input type="text" name="{@name}_year" id="{@name}-year" maxlength="4">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/year/text()"><xsl:value-of select="error/value/year/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/year/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</xsl:when>

			<xsl:when test="@type = 'datetime'">
				<table class="form-date form-float-ele">
					<tr>
						<td class="day">
							<input type="text" name="{@name}_day" id="{@name}" maxlength="2">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/day/text()"><xsl:value-of select="error/value/day/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/day/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="date-separator">.</td>
						<td class="month">
							<input type="text" name="{@name}_month" id="{@name}-month" maxlength="2">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/month/text()"><xsl:value-of select="error/value/month/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/month/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="date-separator">.</td>
						<td class="year">
							<input type="text" name="{@name}_year" id="{@name}-year" maxlength="4">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/year/text()"><xsl:value-of select="error/value/year/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/year/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="datetime-separator"></td>
						<td class="hours">
							<input type="text" name="{@name}_hours" id="{@name}-hours" maxlength="2">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/hours/text()"><xsl:value-of select="error/value/hours/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/hours/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="time-separator">:</td>
						<td class="minutes">
							<input type="text" name="{@name}_minutes" id="{@name}-minutes" maxlength="2">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/minutes/text()"><xsl:value-of select="error/value/minutes/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/minutes/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</xsl:when>

			<xsl:when test="@type = 'calendar'">
				<div class="form-calendar form-float-ele">
					<input type="hidden" name="{@name}" id="{@name}">
						<xsl:attribute name="value">
							<xsl:choose>
								<xsl:when test="error/value/text()"><xsl:value-of select="error/value/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/text()" /></xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
					</input>
					<input type="text" id="{@name}-input" onblur="calendarParseInput('{@name}');" />
					<button onclick="calendarSwitcher('{@name}', event); return false;"><img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" /></button>
					<script type="text/javascript"><xsl:value-of select="concat('calendarInit(&quot;', @name , '&quot;);')" /></script>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'calendar_datetime'">
				<div class="form-calendar form-float-ele">
					<input type="hidden" name="{@name}" id="{@name}">
						<xsl:attribute name="value">
							<xsl:choose>
								<xsl:when test="error/value/date/text()"><xsl:value-of select="error/value/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/date/text()" /></xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
					</input>
                    <input type="text" id="{@name}-input" onblur="calendarParseInput('{@name}');" />
                    <button onclick="calendarSwitcher('{@name}', event); return false;" style="margin-right: 5px;"><img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" /></button>
					<script type="text/javascript"><xsl:value-of select="concat('calendarInit(&quot;', @name , '&quot;);')" /></script>

                    <xsl:variable name="hours-value"><xsl:choose>
                        <xsl:when test="error/value/hours/text()"><xsl:value-of select="error/value/hours/text()" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="value/hours/text()" /></xsl:otherwise>
                    </xsl:choose></xsl:variable>

                    <xsl:variable name="minutes-value"><xsl:choose>
                        <xsl:when test="error/value/minutes/text()"><xsl:value-of select="error/value/minutes/text()" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="value/minutes/text()" /></xsl:otherwise>
                    </xsl:choose></xsl:variable>

                    <table style="float: left;"><tr>
                        <td><select name="{@name}_hours">
                            <xsl:for-each select="additional/hours/item">
                                <option value="{@value}" style="text-align: right;">
                                    <xsl:if test="$hours-value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
                                    <xsl:value-of select="text()" />
                                </option>
                            </xsl:for-each>
                        </select></td>
                        <td style="padding: 0 2px;">:</td>
                        <td><select name="{@name}_minutes">
                            <xsl:for-each select="additional/minutes/item">
                                <option value="{@value}" style="text-align: right;">
                                    <xsl:if test="$minutes-value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
                                    <xsl:value-of select="text()" />
                                </option>
                            </xsl:for-each>
                        </select></td>
                    </tr></table>

					<br clear="all" />
				</div>
			</xsl:when>

			<xsl:when test="@type = 'date_period' or @type = 'datetime_period'">
				<div class="form-calendar form-float-ele">
					<div style="float: left; margin-bottom: 0.5em;">
						<input type="text" id="{@name}-from-input" onblur="calendarParseInput('{@name}-from');" />
						<button style="margin-right: 5px;" onclick="calendarSwitcher('{@name}-from', event); return false;"><img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" /></button>
						<xsl:if test="@type = 'datetime_period'">
							<xsl:variable name="from-hours-value"><xsl:choose>
								<xsl:when test="error/value/from-hours/text()"><xsl:value-of select="error/value/from-hours/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/from-hours/text()" /></xsl:otherwise>
							</xsl:choose></xsl:variable>

							<xsl:variable name="from-minutes-value"><xsl:choose>
								<xsl:when test="error/value/from-minutes/text()"><xsl:value-of select="error/value/from-minutes/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/from-minutes/text()" /></xsl:otherwise>
							</xsl:choose></xsl:variable>

							<table style="float: left;"><tr>
								<td><select name="{@name}_from_hours">
                                    <xsl:for-each select="additional/hours/item">
                                        <option value="{@value}" style="text-align: right;">
                                            <xsl:if test="$from-hours-value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
                                            <xsl:value-of select="text()" />
                                        </option>
                                    </xsl:for-each>
								</select></td>
								<td style="padding: 0 2px;">:</td>
								<td><select name="{@name}_from_minutes">
                                    <xsl:for-each select="additional/minutes/item">
                                        <option value="{@value}" style="text-align: right;">
                                            <xsl:if test="$from-minutes-value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
                                            <xsl:value-of select="text()" />
                                        </option>
                                    </xsl:for-each>
								</select></td>
							</tr></table>
						</xsl:if>
					</div>

					<div style="float: left; margin: 1px 3px 0.5em 3px; font-size: 1.25em;">&mdash;</div>

					<div style="float: left;">
						<input type="text" id="{@name}-till-input" onblur="calendarParseInput('{@name}-till');" />
						<button style="margin-right: 5px;" onclick="calendarSwitcher('{@name}-till', event); return false;"><img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" /></button>
						<xsl:if test="@type = 'datetime_period'">
							<xsl:variable name="till-hours-value"><xsl:choose>
								<xsl:when test="error/value/till-hours/text()"><xsl:value-of select="error/value/till-hours/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/till-hours/text()" /></xsl:otherwise>
							</xsl:choose></xsl:variable>

							<xsl:variable name="till-minutes-value"><xsl:choose>
								<xsl:when test="error/value/till-minutes/text()"><xsl:value-of select="error/value/till-minutes/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/till-minutes/text()" /></xsl:otherwise>
							</xsl:choose></xsl:variable>

							<table style="float: left;"><tr>
								<td><select name="{@name}_till_hours">
                                    <xsl:for-each select="additional/hours/item">
                                        <option value="{@value}" style="text-align: right;">
                                            <xsl:if test="$till-hours-value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
                                            <xsl:value-of select="text()" />
                                        </option>
                                    </xsl:for-each>
								</select></td>
								<td style="padding: 0 2px;">:</td>
								<td><select name="{@name}_till_minutes">
                                    <xsl:for-each select="additional/minutes/item">
                                        <option value="{@value}" style="text-align: right;">
                                            <xsl:if test="$till-minutes-value = @value"><xsl:attribute name="selected">true</xsl:attribute></xsl:if>
                                            <xsl:value-of select="text()" />
                                        </option>
                                    </xsl:for-each>
								</select></td>
							</tr></table>
						</xsl:if>
					</div>

					<input type="hidden" name="{@name}_from" id="{@name}-from">
						<xsl:attribute name="value">
							<xsl:choose>
								<xsl:when test="error/value/from/text()"><xsl:value-of select="error/value/from/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/from/text()" /></xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
					</input>
					<input type="hidden" name="{@name}_till" id="{@name}-till">
						<xsl:attribute name="value">
							<xsl:choose>
								<xsl:when test="error/value/till/text()"><xsl:value-of select="error/value/till/text()" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="value/till/text()" /></xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
					</input>

					<script type="text/javascript">
						<xsl:value-of select="concat('calendarInit(&quot;', @name , '-from&quot;);')" />
						<xsl:value-of select="concat('calendarInit(&quot;', @name , '-till&quot;);')" />
					</script>

					<br clear="all" />
				</div>
			</xsl:when>

			<xsl:when test="@type = 'image'">
				<div class="form-float-ele">
					<xsl:choose>
						<xsl:when test="value/url/text() and value/width/text()">
							<div class="field-image-params">
								<table class="chooser-item">
									<tr>
										<td>
											<input type="checkbox" name="{@name}_delete" id="{generate-id()}" value="1" />
											<input type="hidden" name="{@name}_present" value="{value/path/text()}" />
										</td>
										<td class="chooser-label">
											<label for="{generate-id()}">
												<xsl:text>Удалить</xsl:text><br />

												<a href="{value/url/text()}" target="_blank">Загруженное изображение</a>
                                                <br /><br />

                                                <xsl:variable name="max-length">300</xsl:variable>

                                                <img src="{value/url/text()}" align="left">
                                                    <xsl:choose>
                                                        <xsl:when test="value[width &lt;= $max-length and height &lt;= $max-length]">
                                                            <xsl:attribute name="class">preview</xsl:attribute>
                                                            <xsl:attribute name="width">
                                                                <xsl:value-of select="value/width" />
                                                            </xsl:attribute>
                                                            <xsl:attribute name="height">
                                                                <xsl:value-of select="value/height" />
                                                            </xsl:attribute>
                                                        </xsl:when>
                                                        <xsl:when test="value[width > height]">
                                                            <xsl:attribute name="class">preview-resized</xsl:attribute>
                                                            <xsl:attribute name="width">
                                                                <xsl:value-of select="$max-length" />
                                                            </xsl:attribute>
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <xsl:attribute name="class">preview-resized</xsl:attribute>
                                                            <xsl:attribute name="height">
                                                                <xsl:value-of select="$max-length" />
                                                            </xsl:attribute>
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                </img>

												<xsl:value-of select="concat(value/width/text(), '&times;', value/height/text(), ' ', value/size/text(), '&nbsp;КБ')" />
											</label>
										</td>
									</tr>
								</table>
								<div class="field-image-replace">
									<xsl:text>Заменить:</xsl:text><br />
									<input type="file" name="{@name}" id="form-ele-{@name}" class="file" />
								</div>
							</div>
						</xsl:when>
						<xsl:otherwise>
							<input type="file" name="{@name}" id="form-ele-{@name}" class="file" />
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'year'">
				<div class="form-float-ele">
					<input name="{@name}" id="form-ele-{@name}" type="text" maxlength="4" class="{@type}">
						<xsl:attribute name="value">
							<xsl:choose>
								<xsl:when test="error/value/text()"><xsl:value-of select="error/value/text()" /></xsl:when>
								<xsl:when test="value/text()"><xsl:value-of select="value/text()" /></xsl:when>
							</xsl:choose>
						</xsl:attribute>
					</input>
				</div>
			</xsl:when>

            <xsl:when test="(@type = 'integer' or @type = 'float') and @is-readonly">
                <div class="form-float-ele">
                    <xsl:value-of select="value" />
                </div>
            </xsl:when>

			<xsl:when test="@type = 'integer' or @type = 'float'">
				<div class="form-float-ele">
					<input name="{@name}" id="form-ele-{@name}" type="text" maxlength="10" class="{@type}">
						<xsl:attribute name="value">
							<xsl:choose>
								<xsl:when test="error/value/text()"><xsl:value-of select="error/value/text()" /></xsl:when>
								<xsl:when test="value/text()"><xsl:value-of select="value/text()" /></xsl:when>
								<xsl:when test="@is-required">0</xsl:when>
							</xsl:choose>
						</xsl:attribute>
					</input>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'adding_files'">
				<div>
					<xsl:if test="description">
						<div class="add-files-description"><xsl:value-of select="description/text()" disable-output-escaping="yes" /></div>
					</xsl:if>
					<div id="add-form-files-{@name}" class="add-files" onclick="addFormFileInputs('{@name}');">Добавить</div>
				</div>

				<xsl:for-each select="additional[*[@path]]">
					<div class="files">
						<xsl:for-each select="*[@path]">
							<div class="file">
								<span onclick="deleteFile(this, '{@path}');" title="Удалить файл немедленно?">&times;</span>
								<xsl:text>&nbsp;</xsl:text>
								<a href="{@uri}"><xsl:value-of select="@filename" /></a>
								<xsl:text> </xsl:text>
								<xsl:value-of select="size/text()" disable-output-escaping="yes" />
							</div>
						</xsl:for-each>
					</div>
				</xsl:for-each>
			</xsl:when>

			<xsl:when test="@type = 'password'">
				<div class="form-float-ele">
					<table class="form-password">
						<tr>
							<td class="password"><input type="password" name="{@name}" id="form-ele-{@name}" value="{value/password/text()}" maxlength="255" /></td>
							<td class="check"><input type="password" name="{@name}_check" id="form-ele-{@name}-check" value="{value/password/text()}" maxlength="255" /></td>
						</tr>
						<tr>
							<td colspan="2">
								<label for="form-ele-{@name}">Введите пароль</label>
								<xsl:text> </xsl:text>
								<label for="form-ele-{@name}-check">и повторите для проверки.</label>
							</td>
						</tr>
					</table>
				</div>
			</xsl:when>

			<xsl:when test="@type = 'phone'">
				<table class="form-phone form-float-ele">
					<tr>
						<td class="code">
							<input type="text" name="{@name}_code" id="form-ele-{@name}" maxlength="5">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/code/text()"><xsl:value-of select="error/value/code/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/code/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
						<td class="number">
							<input type="text" name="{@name}_number" id="form-ele-{@name}-number" maxlength="10">
								<xsl:attribute name="value">
									<xsl:choose>
										<xsl:when test="error/value/number/text()"><xsl:value-of select="error/value/number/text()" /></xsl:when>
										<xsl:otherwise><xsl:value-of select="value/number/text()" /></xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<xsl:if test="not(preceding::element[@type = 'phone'])">
						<tr>
							<td><label for="form-ele-{@name}">Код</label></td>
							<td><label for="form-ele-{@name}-number">Номер</label></td>
						</tr>
					</xsl:if>
				</table>
			</xsl:when>

			<!--
			Список ссылок на другой модуль
			-->
			<xsl:when test="@type = 'module_items'">
				<a
					href="{additional/module-items/@module-uri}?parent_id={ancestor::module/@id}&amp;NEW"
					class="add-element">Добавить</a>
				<br clear="all" />

				<div class="form-float-ele">
					<xsl:choose>
						<xsl:when test="additional[module-items[item]]">
							<xsl:for-each select="additional/module-items/item">
								<a href="{parent::node()/@module-uri}?id={@id}">
									<xsl:if test="not(@is-published) and not(@is-published)">
										<xsl:attribute name="class">hidden</xsl:attribute>
									</xsl:if>

									<xsl:value-of select="title" disable-output-escaping="yes" />
								</a>

								<xsl:if test="position() != last()"><br /></xsl:if>
							</xsl:for-each>
						</xsl:when>
						<xsl:otherwise>
							<p>Нет</p>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:when>

			<!--
			Список элементов из таблицы с тройным
			составным первичным ключом.

			Ожидается:
            <additional>
                <values>
                    <value>
                        <key-1 id="47" />
                        <key-2 id="59" />
                        <key-3 id="1" />
                    </value>
                </values>

                <options is-key2="true" key="employee_id">
                    <option value="59"><![CDATA[Брусенский Кирилл]]></option>
                    ...
                </options>

                <options is-key3="true" key="position_id">
                    <option value="1"><![CDATA[Веб-технолог]]></option>
                    ...
                </options>
            </additional>
			-->
			<xsl:when test="@type = 'triple_link'">
                <div class="form-float-ele">
                    <div class="function"
                         style="float: left;"
                         onclick="addTripleLink('{@name}');">Добавить</div>

                    <div id="{@name}" style="clear: both;">
                        <xsl:apply-templates select="additional/values/value" mode="triple-link">
                            <xsl:sort select="sort-order/text()" />
                        </xsl:apply-templates>
                    </div>
                </div>
            </xsl:when>

			<xsl:otherwise>
				<xsl:apply-templates select="self::node()" mode="project-form-element" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="item" mode="checkbox">
		<xsl:param name="without-table">false</xsl:param>

		<xsl:variable name="value" select="@value" />
		<xsl:variable name="content">
			<tr>
				<td>
					<input type="checkbox" name="{ancestor::node()[2]/@name}[]" id="{generate-id()}" value="{$value}">
						<xsl:choose>
							<xsl:when test="ancestor::node()[2]/error/value/item">
								<xsl:if test="ancestor::node()[2]/error/value/item[text() = $value]">
									<xsl:attribute name="checked">true</xsl:attribute>
								</xsl:if>
							</xsl:when>
							<xsl:when test="ancestor::node()[2]/value/item[text() = $value]">
								<xsl:attribute name="checked">true</xsl:attribute>
							</xsl:when>
						</xsl:choose>
					</input>
				</td>
				<td class="chooser-label" width="99%">
					<label for="{generate-id()}"><xsl:value-of select="text()" disable-output-escaping="yes" /></label>
				</td>
			</tr>
		</xsl:variable>

		<xsl:choose>
			<xsl:when test="$without-table = 'true'">
				<xsl:copy-of select="$content" />
			</xsl:when>
			<xsl:otherwise>
				<table class="chooser-item">
					<xsl:copy-of select="$content" />
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>


    <!--
    Список элементов из таблицы с тройным
    составным первичным ключом
    -->
    <xsl:template match="value" mode="triple-link">
        <xsl:variable name="name"
                      select="concat(ancestor::element/@name, '_', position())" />

        <table class="triple-link-item">
            <tr>
                <td class="system">
                    <div onclick="deleteTripleLink(this);">&times;</div>
                    <input type="hidden"
                           name="{ancestor::element/@name}[]"
                           value="{position()}" />
                </td>
                <td>
                    <xsl:apply-templates select="ancestor::additional/options[@is-key-2]"
                                         mode="triple-link">
                        <xsl:with-param name="name" select="$name" />
                        <xsl:with-param name="selected-id" select="key-2/@id" />
                    </xsl:apply-templates>
                </td>
                <td>
                    <xsl:apply-templates select="ancestor::additional/options[@is-key-3]"
                                         mode="triple-link">
                        <xsl:with-param name="name" select="$name" />
                        <xsl:with-param name="selected-id" select="key-3/@id" />
                    </xsl:apply-templates>
                </td>
            </tr>
        </table>
    </xsl:template>

    <!--
    Ожидается:
    <http-request type = 'triple-link' name="element-name" position="1">
        <content>
            <options is-key2="true" key="employee_id">
                <option value="59"><![CDATA[Брусенский Кирилл]]></option>
                ...
            </options>

            <options is-key3="true" key="position_id">
                <option value="1"><![CDATA[Веб-технолог]]></option>
                ...
            </options>
        </content>
    </http-request>
    -->
    <xsl:template name="triple-link-item">
        <xsl:variable name="name"
                      select="concat('new_', @name, '_', @position)" />

        <table class="triple-link-item">
            <tr>
                <td class="system">
                    <div onclick="deleteTripleLink(this);">&times;</div>
                    <input type="hidden" name="new_{@name}[]" value="{@position}" />
                </td>
                <td>
                    <xsl:apply-templates select="content/options[@is-key-2]" mode="triple-link">
                        <xsl:with-param name="name" select="$name" />
                    </xsl:apply-templates>
                </td>
                <td>
                    <xsl:apply-templates select="content/options[@is-key-3]" mode="triple-link">
                        <xsl:with-param name="name" select="$name" />
                    </xsl:apply-templates>
                </td>
            </tr>
        </table>
    </xsl:template>

    <xsl:template match="options" mode="triple-link">
        <xsl:param name="selected-id" />
        <xsl:param name="name" />

        <select name="{$name}_{@key}">
            <xsl:for-each select="option">
                <option value="{@value}">
                    <xsl:choose>
                        <xsl:when test="@value = $selected-id">
                            <xsl:attribute name="selected">true</xsl:attribute>
                        </xsl:when>
                        <xsl:when test="@is-selected">
                            <xsl:attribute name="selected">true</xsl:attribute>
                        </xsl:when>
                    </xsl:choose>
                    <xsl:value-of select="text()" disable-output-escaping="yes" />
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>

    <xsl:template match="upload-file">
        <xsl:apply-templates select="html" />

        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" />
            <xsl:text> </xsl:text>
            <input type="submit" name="submit" value="Загрузить" />
        </form>

        <xsl:if test="error">
            <p style="color: #f00; font-size: 0.84em;">
                <xsl:value-of select="error/text()" disable-output-escaping="yes" />
            </p>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
