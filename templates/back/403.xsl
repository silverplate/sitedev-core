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
				<link href="/cms/f/css/forms.css" type="text/css" rel="stylesheet" />
				<link href="/cms/f/css/403.css" type="text/css" rel="stylesheet" />

				<script src="/cms/f/js/403.js" type="text/javascript" />
			</head>
			<body>
				<xsl:attribute name="onload">
					<xsl:text>document.getElementById('</xsl:text>
					<xsl:choose>
						<xsl:when test="/node()/system/session[@action = 7]">auth-email</xsl:when>
						<xsl:otherwise>auth-login</xsl:otherwise>
					</xsl:choose>
					<xsl:text>').focus();</xsl:text>
				</xsl:attribute>

				<table width="100%" height="100%">
					<tr>
						<td height="99%" valign="top">
							<xsl:apply-templates select="system" mode="navigation" />

							<form action="{/node()/url}" method="post">
								<table class="auth-form">
									<tr>
										<td class="auth-login">
											<label for="auth-login">Логин</label>
											<input type="text" name="auth_login" id="auth-login" maxlength="30" tabindex="1" />
										</td>
										<td class="auth-password">
											<label for="auth-password">Пароль</label>
											<input type="password" name="auth_password" id="auth-password" maxlength="255" tabindex="2" />
										</td>
									</tr>
									<tr>
										<td colspan="3">
											<table class="chooser-item">
												<tr>
													<td><input type="checkbox" name="auth_is_remember_me" id="auth-is-remember-me" value="1" tabindex="3" /></td>
													<td class="chooser-label"><label for="auth-is-remember-me">Авторизовывайте меня сразу</label></td>
												</tr>
											</table>
											<input type="submit" name="auth_submit" id="auth-submit" value="Войти" tabindex="5" />
										</td>
									</tr>
									<xsl:apply-templates select="/node()/system/session[@action = 3 or @action = 8 or @action = 9]" />
								</table>

								<div id="auth-forgot">
									<xsl:if test="/node()/system/session[@action = 6 or @action = 7]">
										<xsl:attribute name="style">display: none;</xsl:attribute>
									</xsl:if>
									<span onclick="passwordReminder(true);">Я не помню пароль</span>
								</div>

								<table id="auth-reminder">
									<xsl:if test="/node()/system/session[@action = 6 or @action = 7]">
										<xsl:attribute name="style">display: block;</xsl:attribute>
									</xsl:if>
									<tr>
										<td class="auth-email">
											<label for="auth-email">Сделайте мне новый пароль. Мой адрес</label>
											<input type="text" name="auth_email" id="auth-email" maxlength="255" />
										</td>
									</tr>
									<tr>
										<td><input type="submit" name="auth_reminder_submit" id="auth-reminder-submit" value="Пришлите пароль" /></td>
									</tr>
									<xsl:apply-templates select="/node()/system/session[@action = 6 or @action = 7]" />
								</table>
							</form>
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

	<xsl:template match="session[@action = 3]">
		<tr><td class="error" colspan="3">Неправильная комбинация логина и&nbsp;пароля.</td></tr>
	</xsl:template>

	<xsl:template match="session[@action = 6]">
		<tr><td class="success">Инструкции по&nbsp;смене пароля высланы на&nbsp;указанный адрес.</td></tr>
	</xsl:template>

	<xsl:template match="session[@action = 7]">
		<tr><td class="error">У пользователя с&nbsp;указанным адресом доступа&nbsp;нет.</td></tr>
	</xsl:template>

	<xsl:template match="session[@action = 8]">
		<tr><td class="success" colspan="3">Новый доступ выслан на&nbsp;ваш электронный адрес.</td></tr>
	</xsl:template>

	<xsl:template match="session[@action = 9]">
		<tr><td class="error" colspan="3">Изменить пароль не&nbsp;удалось. Свяжитесь со&nbsp;службой поддерки для&nbsp;выяснения причин этого.</td></tr>
	</xsl:template>
</xsl:stylesheet>
