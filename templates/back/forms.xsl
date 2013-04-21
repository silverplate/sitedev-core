<?xml version="1.0"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../../library/Ext/entities.dtd">

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
                    
                    <xsl:if test="label-description">
                        <div class="description">
                            <xsl:value-of select="label-description" disable-output-escaping="yes" />
                        </div>
                    </xsl:if>
                </td>
            </xsl:if>

            <td>
                <xsl:attribute name="class">
                    <xsl:text>input</xsl:text>
                    <xsl:if test="count(parent::node()/element) = 1"> alone</xsl:if>
                </xsl:attribute>

                <xsl:apply-templates select="self::node()" />

                <!--
                Прежняя реализация
                @todo Убрать после перехода на новую реализацию.
                -->
                <xsl:if test="@update-type != '' and
                              @update-type != 'no-update' and
                              @update-type != 'success'">

                    <div class="field-error-message">
                        <xsl:choose>
                            <xsl:when test="error-message">
                                <xsl:value-of select="error-message"
                                              disable-output-escaping="yes" />
                            </xsl:when>
                            <xsl:when test="@update-type = 'error-required'">Поле обязательно для&nbsp;заполнения.</xsl:when>
                            <xsl:when test="@update-type = 'error-spelling'">Некорректное значение.</xsl:when>
                            <xsl:when test="@update-type = 'error-exist'">Значение уже&nbsp;используется.</xsl:when>
                            <xsl:otherwise>Некорректное значение.</xsl:otherwise>
                        </xsl:choose>
                    </div>
                </xsl:if>

                <xsl:if test="
                    @update-status != 'success' and
                    @update-status != 'no-update'
                ">
                    <div class="field-error-message"><xsl:choose>
                        <xsl:when test="status-error-message">
                            <xsl:value-of select="status-error-message"
                                          disable-output-escaping="yes" />
                        </xsl:when>
                        <xsl:when test="@update-type = 'error-required'">Поле обязательно для&nbsp;заполнения.</xsl:when>
                        <!-- <xsl:when test="@update-type = 'error-spelling'">Некорректное значение.</xsl:when> -->
                        <xsl:when test="@update-type = 'error-exist'">Значение уже&nbsp;используется.</xsl:when>
                        <xsl:otherwise>Некорректное значение.</xsl:otherwise>
                    </xsl:choose></div>
                </xsl:if>

                <xsl:if test="input-description">
                    <div class="description">
                        <xsl:value-of
                            select="input-description"
                            disable-output-escaping="yes"
                        />
                    </div>
                </xsl:if>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="element[@type = 'boolean']">
        <div class="form-float-ele"><xsl:choose>
            <xsl:when test="@is-readonly and value[text() = '1']">
                <xsl:attribute name="title">Да</xsl:attribute>
                <xsl:text>&bull;</xsl:text>
            </xsl:when>

            <xsl:when test="@type = 'boolean' and @is-readonly">Нет</xsl:when>

            <xsl:otherwise>
                <input type="checkbox" name="{@name}" id="form-ele-{@name}" value="1">
                    <xsl:if test="value and value[text() != '0']">
                        <xsl:attribute name="checked">true</xsl:attribute>
                    </xsl:if>
                </input>
            </xsl:otherwise>
        </xsl:choose></div>
    </xsl:template>

    <xsl:template match="element[@type = 'name']">
        <table class="form-name form-float-ele">
            <tr>
                <td class="last-name">
                    <input type="text" name="{@name}_last_name" id="form-ele-{@name}" maxlength="255">
                        <xsl:attribute name="value"><xsl:choose>
                            <xsl:when test="error/value/last-name">
                                <xsl:value-of select="error/value/last-name" />
                            </xsl:when><xsl:otherwise>
                                <xsl:value-of select="value/last-name" />
                            </xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                    </input>
                </td>
                <td class="first-name">
                    <input type="text" name="{@name}_first_name" id="form-ele-{@name}-first-name" maxlength="255">
                        <xsl:attribute name="value"><xsl:choose>
                            <xsl:when test="error/value/first-name">
                                <xsl:value-of select="error/value/first-name" />
                            </xsl:when><xsl:otherwise>
                                <xsl:value-of select="value/first-name" />
                            </xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                    </input>
                </td>
                <td class="middle-name">
                    <input type="text" name="{@name}_middle_name" id="form-ele-{@name}-middle-name" maxlength="255">
                        <xsl:attribute name="value"><xsl:choose>
                            <xsl:when test="error/value/middle-name">
                                <xsl:value-of select="error/value/middle-name" />
                            </xsl:when><xsl:otherwise>
                                <xsl:value-of select="value/middle-name" />
                            </xsl:otherwise>
                        </xsl:choose></xsl:attribute>
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
                    <td><label for="form-ele-{@name}-middle-name">Отчество</label></td>
                </tr>
            </xsl:if>
        </table>
    </xsl:template>

    <xsl:template match="element[@type = 'document-parent-id']">
        <input type="hidden" name="{@name}" id="form-ele-{@name}" value="{value/text()}" />

        <div id="{@name}-change">
            <xsl:if test="not(ancestor::node()[name() = 'module']/@id)">
                <xsl:attribute name="style">display: none;</xsl:attribute>
            </xsl:if>

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
    </xsl:template>

    <xsl:template match="element[@type = 'multiple-tree' or
                                 @type = 'single-tree']">

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
            <xsl:when test="@type = 'single-tree'">single</xsl:when>
            <xsl:otherwise>multiple</xsl:otherwise>
        </xsl:choose></xsl:variable>

        <script type="text/javascript">
            <xsl:value-of select="concat('var formTreeValues_', $field-name, ' = new Array(')" />
            <xsl:choose>
                <xsl:when test="@type = 'single-tree' and value/text()"><xsl:value-of select="concat('&quot;', value/text(), '&quot;')" /></xsl:when>
                <xsl:when test="@type = 'single-tree' and not(value/item)">""</xsl:when>
                <xsl:when test="value/item"><xsl:for-each select="value/item">
                    <xsl:value-of select="concat('&quot;', text(), '&quot;')" />
                    <xsl:if test="position() != last()">, </xsl:if>
                </xsl:for-each></xsl:when>
            </xsl:choose>
            <xsl:value-of select="concat('); treeLoad(&quot;', $field-name, '-tree&quot;, &quot;', $module-name, '&quot;, &quot;', $field-name, '&quot;, &quot;&quot;, &quot;', $type, '&quot;);')" />
        </script>
    </xsl:template>

    <xsl:template match="element[@type = 'chooser' or
                                 @type = 'select' or
                                 @type = 'radio']">

        <xsl:variable name="value"><xsl:choose>
            <xsl:when test="error[value]"><xsl:value-of select="error/value" /></xsl:when>
            <xsl:otherwise><xsl:value-of select="value" /></xsl:otherwise>
        </xsl:choose></xsl:variable>

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
                        <table class="chooser-item"><tr>
                            <td>
                                <input
                                    type="radio"
                                    name="{ancestor::node()[2]/@name}"
                                    id="{generate-id()}"
                                    value="{@value}"
                                >
                                    <xsl:if test="@value = $value or (position() = 1 and $value = '')">
                                        <xsl:attribute name="checked">true</xsl:attribute>
                                    </xsl:if>
                                </input>
                            </td>
                            <td class="chooser-label">
                                <label for="{generate-id()}">
                                    <xsl:value-of select="text()" disable-output-escaping="yes" />
                                </label>
                            </td>
                        </tr></table>
                    </xsl:for-each>
                </xsl:when>

                <xsl:otherwise>Нет</xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>

    <xsl:template match="element[@type = 'multiple']">
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
    </xsl:template>

    <xsl:template match="element[@type = 'text' or
                                 @type = 'short-text' or
                                 @type = 'large-text']">

        <div class="form-float-ele">
            <textarea name="{@name}" id="form-ele-{@name}" class="{@type}">
                <xsl:choose>
                    <xsl:when test="error[value]">
                        <xsl:value-of select="error/value" disable-output-escaping="no" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="value" disable-output-escaping="no" />
                    </xsl:otherwise>
                </xsl:choose>
            </textarea>
        </div>
    </xsl:template>

    <xsl:template match="element[@type = 'image']">
        <div class="form-float-ele">
            <xsl:choose>
                <xsl:when test="value[uri and width]">
                    <div class="field-image-params">
                        <table class="chooser-item">
                            <tr>
                                <td>
                                    <input type="checkbox" name="{@name}_delete" id="{generate-id()}" value="1" />
                                    <input type="hidden" name="{@name}_present" value="{value/path}" />
                                </td>
                                <td class="chooser-label">
                                    <label for="{generate-id()}">
                                        <xsl:text>Удалить</xsl:text><br />

                                        <a href="{value/uri}" target="_blank">Загруженное изображение</a>
                                        <br /><br />

                                        <xsl:variable name="max-length">300</xsl:variable>

                                        <img src="{value/uri}" align="left">
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

                                        <xsl:value-of select="value/width" />
                                        <xsl:text>&times;</xsl:text>
                                        <xsl:value-of select="value/height" />
                                        <xsl:text> </xsl:text>
                                        <xsl:value-of select="value/size" />
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
    </xsl:template>

    <xsl:template match="element[@type = 'year']">
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
    </xsl:template>

    <xsl:template match="element[(@type = 'integer' or @type = 'float') and @is-readonly]">
        <div class="form-float-ele">
            <xsl:value-of select="value" />
        </div>
    </xsl:template>

    <xsl:template match="element[@type = 'integer' or @type = 'float']">
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
    </xsl:template>

    <xsl:template match="element[@type = 'files']">
        <div>
            <!-- <xsl:if test="description">
                <div class="add-files-description">
                    <xsl:value-of select="description/text()" disable-output-escaping="yes" />
                </div>
            </xsl:if> -->

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
    </xsl:template>

    <xsl:template match="element[@type = 'password']">
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
    </xsl:template>

    <xsl:template match="element[@type = 'phone']">
        <table class="form-phone form-float-ele">
            <tr>
                <td class="code">
                    <input type="text" name="{@name}_code" id="form-ele-{@name}" maxlength="5">
                        <xsl:attribute name="value"><xsl:choose>
                            <xsl:when test="error[value[code]]">
                                <xsl:value-of select="error/value/code" />
                            </xsl:when><xsl:otherwise>
                                <xsl:value-of select="value/code" />
                            </xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                    </input>
                </td>
                <td class="number">
                    <input type="text" name="{@name}_number" id="form-ele-{@name}-number" maxlength="10">
                        <xsl:attribute name="value"><xsl:choose>
                            <xsl:when test="error[value[number]]">
                                <xsl:value-of select="error/value/number" />
                            </xsl:when><xsl:otherwise>
                                <xsl:value-of select="value/number" />
                            </xsl:otherwise>
                        </xsl:choose></xsl:attribute>
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
    </xsl:template>


    <!--
    Список ссылок на другой модуль
    -->

    <xsl:template match="element[@type = 'module-items']">
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
                </xsl:when><xsl:otherwise>
                    <p>Нет</p>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>


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

    <xsl:template match="element[@type = 'triple-link']">
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
    </xsl:template>

    <xsl:template match="element[@type = 'date']">
        <xsl:call-template name="calendar-ele" />

        <!--
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
        -->
    </xsl:template>

    <xsl:template match="element[@type = 'datetime']">
        <xsl:call-template name="calendar-datetime-ele" />

        <!--
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
        -->
    </xsl:template>

    <xsl:template match="element[@type = 'calendar']" name="calendar-ele">
        <div class="form-calendar form-float-ele">
            <xsl:call-template name="calendar">
                <xsl:with-param name="name">
                    <xsl:value-of select="@name" />
                </xsl:with-param>

                <xsl:with-param name="value"><xsl:choose>
                    <xsl:when test="error/value">
                        <xsl:value-of select="error/value" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="value" />
                    </xsl:otherwise>
                </xsl:choose></xsl:with-param>
            </xsl:call-template>
        </div>
    </xsl:template>

    <xsl:template
        match="element[@type = 'calendar-datetime']"
        name="calendar-datetime-ele"
    >
        <div class="form-calendar form-float-ele">
            <xsl:call-template name="calendar">
                <xsl:with-param name="name">
                    <xsl:value-of select="concat(@name, '_date')" />
                </xsl:with-param>

                <xsl:with-param name="value"><xsl:choose>
                    <xsl:when test="error/value/date">
                        <xsl:value-of select="error/value/date" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="value/date" />
                    </xsl:otherwise>
                </xsl:choose></xsl:with-param>
            </xsl:call-template>

            <xsl:call-template name="time" />

            <br clear="all" />
        </div>
    </xsl:template>

    <xsl:template match="element[@type = 'date-period']">
        <div class="form-calendar form-float-ele">
            <div style="float: left; margin-bottom: 0.5em;">
                <xsl:call-template name="calendar">
                    <xsl:with-param name="name">
                        <xsl:value-of select="concat(@name, '_from')" />
                    </xsl:with-param>

                    <xsl:with-param name="value"><xsl:choose>
                        <xsl:when test="error/value/from">
                            <xsl:value-of select="error/value/from" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="value/from" />
                        </xsl:otherwise>
                    </xsl:choose></xsl:with-param>
                </xsl:call-template>
            </div>

            <div style="float: left; margin: 1px 5px 0.5em 3px; font-size: 1.25em;">&mdash;</div>

            <div style="float: left;">
                <xsl:call-template name="calendar">
                    <xsl:with-param name="name">
                        <xsl:value-of select="concat(@name, '_till')" />
                    </xsl:with-param>

                    <xsl:with-param name="value"><xsl:choose>
                        <xsl:when test="error/value/till">
                            <xsl:value-of select="error/value/till" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="value/till" />
                        </xsl:otherwise>
                    </xsl:choose></xsl:with-param>
                </xsl:call-template>
            </div>

            <br clear="all" />
        </div>
    </xsl:template>

    <xsl:template match="element[@type = 'datetime-period']">
        <div class="form-calendar form-float-ele">
            <div style="float: left; margin-bottom: 0.5em;">
                <xsl:call-template name="calendar">
                    <xsl:with-param name="name">
                        <xsl:value-of select="concat(@name, '_from_date')" />
                    </xsl:with-param>

                    <xsl:with-param name="value"><xsl:choose>
                        <xsl:when test="error/value/from-date">
                            <xsl:value-of select="error/value/from-date" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="value/from-date" />
                        </xsl:otherwise>
                    </xsl:choose></xsl:with-param>
                </xsl:call-template>

                <xsl:call-template name="time">
                    <xsl:with-param name="prefix">from</xsl:with-param>
                </xsl:call-template>
            </div>

            <div style="float: left; margin: 1px 5px 0.5em 3px; font-size: 1.25em;">&mdash;</div>
            
            <div style="float: left;">
                <xsl:call-template name="calendar">
                    <xsl:with-param name="name">
                        <xsl:value-of select="concat(@name, '_till_date')" />
                    </xsl:with-param>

                    <xsl:with-param name="value"><xsl:choose>
                        <xsl:when test="error/value/till-date">
                            <xsl:value-of select="error/value/till-date" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="value/till-date" />
                        </xsl:otherwise>
                    </xsl:choose></xsl:with-param>
                </xsl:call-template>

                <xsl:call-template name="time">
                    <xsl:with-param name="prefix">till</xsl:with-param>
                </xsl:call-template>
            </div>

            <br clear="all" />
        </div>
    </xsl:template>

    <xsl:template match="element">
        <div class="form-float-ele">
            <xsl:choose>
                <xsl:when test="@is-readonly">
                    <xsl:value-of select="value" />
                </xsl:when>
                <xsl:otherwise>
                    <input name="{@name}" id="form-ele-{@name}" type="text" maxlength="255" class="{@type}">
                        <xsl:attribute name="value">
                            <xsl:choose>
                                <xsl:when test="error[value]"><xsl:value-of select="error/value" /></xsl:when>
                                <xsl:otherwise><xsl:value-of select="value" /></xsl:otherwise>
                            </xsl:choose>
                        </xsl:attribute>
                    </input>
                </xsl:otherwise>
            </xsl:choose>
        </div>
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


    <!--
    Календарь
    -->
    
    <xsl:template name="calendar">
        <xsl:param name="name" />
        <xsl:param name="value" />

        <input type="hidden" name="{$name}" id="{$name}" value="{$value}" />

        <input
            type="text"
            id="{$name}-input"
            onblur="calendarParseInput('{$name}');"
        />

        <button onclick="calendarSwitcher('{$name}', event); return false;">
            <img src="/cms/f/calendar/btn.gif" width="25" height="13" alt="" />
        </button>

        <script type="text/javascript">calendarInit("<xsl:value-of select="$name" />");</script>
    </xsl:template>


    <!--
    Время
    -->

    <xsl:template name="time">
        <xsl:param name="prefix" />

        <xsl:variable name="hour-ele-name">
            <xsl:value-of select="@name" />
            <xsl:if test="$prefix">
                <xsl:value-of select="$prefix" />
                <xsl:text>_</xsl:text>
            </xsl:if>
            <xsl:text>hour</xsl:text>
        </xsl:variable>

        <xsl:variable name="hour-node-name">
            <xsl:if test="$prefix">
                <xsl:value-of select="$prefix" />
                <xsl:text>-</xsl:text>
            </xsl:if>
            <xsl:text>hour</xsl:text>
        </xsl:variable>

        <xsl:variable name="hours"><xsl:choose>
            <xsl:when test="error[value[node()[name() = $hour-hode-name]]]">
                <xsl:value-of select="error/value/node()[name() = $hour-node-name]" />
            </xsl:when><xsl:otherwise>
                <xsl:value-of select="value/node()[name() = $hour-node-name]" />
            </xsl:otherwise>
        </xsl:choose></xsl:variable>

        <xsl:variable name="minute-node-name">
            <xsl:if test="$prefix">
                <xsl:value-of select="$prefix" />
                <xsl:text>-</xsl:text>
            </xsl:if>
            <xsl:text>minute</xsl:text>
        </xsl:variable>

        <xsl:variable name="minute-ele-name">
            <xsl:value-of select="@name" />
            <xsl:if test="$prefix">
                <xsl:value-of select="$prefix" />
                <xsl:text>_</xsl:text>
            </xsl:if>
            <xsl:text>minute</xsl:text>
        </xsl:variable>

        <xsl:variable name="minutes"><xsl:choose>
            <xsl:when test="error[value[node()[name() = $minute-hode-name]]]">
                <xsl:value-of select="error/value/node()[name() = $minute-node-name]" />
            </xsl:when><xsl:otherwise>
                <xsl:value-of select="value/node()[name() = $minute-node-name]" />
            </xsl:otherwise>
        </xsl:choose></xsl:variable>

        <table style="float: left; margin-left: 5px;"><tr>
            <td><select name="{$hour-ele-name}">
                <xsl:for-each select="additional/hour/item">
                    <xsl:variable name="value"><xsl:choose>
                        <xsl:when test="@value"><xsl:value-of select="@value" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="text()" /></xsl:otherwise>
                    </xsl:choose></xsl:variable>

                    <option value="{$value}" style="text-align: right;">
                        <xsl:if test="$hours = $value">
                            <xsl:attribute name="selected">1</xsl:attribute>
                        </xsl:if>

                        <xsl:value-of select="text()" />
                    </option>
                </xsl:for-each>
            </select></td>

            <td style="padding: 0 2px;">:</td>

            <td><select name="{$minute-ele-name}">
                <xsl:for-each select="additional/minute/item">
                    <xsl:variable name="value"><xsl:choose>
                        <xsl:when test="@value"><xsl:value-of select="@value" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="text()" /></xsl:otherwise>
                    </xsl:choose></xsl:variable>

                    <option value="{$value}" style="text-align: right;">
                        <xsl:if test="$minutes = $value">
                            <xsl:attribute name="selected">1</xsl:attribute>
                        </xsl:if>

                        <xsl:value-of select="text()" />
                    </option>
                </xsl:for-each>
            </select></td>
        </tr></table>
    </xsl:template>
</xsl:stylesheet>
