{{/*
Expand the name of the chart.
*/}}
{{- define "pays-de-blain-vis-mon-metier.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "pays-de-blain-vis-mon-metier.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "pays-de-blain-vis-mon-metier.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "pays-de-blain-vis-mon-metier.labels" -}}
helm.sh/chart: {{ include "pays-de-blain-vis-mon-metier.chart" . }}
{{ include "pays-de-blain-vis-mon-metier.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "pays-de-blain-vis-mon-metier.selectorLabels" -}}
app.kubernetes.io/name: {{ include "pays-de-blain-vis-mon-metier.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "pays-de-blain-vis-mon-metier.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "pays-de-blain-vis-mon-metier.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}

{{- define "metabase.labels" -}}
helm.sh/chart: {{ include "pays-de-blain-vis-mon-metier.chart" . }}
{{ include "metabase.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{- define "metabase.selectorLabels" -}}
app.kubernetes.io/name: metabase
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}
