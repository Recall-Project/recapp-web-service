{
    "_id": "_design/analysis",
    "_rev": "2-0255a67be1c78cf19b91cd5311eafeec",
    "language": "javascript",
    "views": {
    "usersurveys": {
        "map": "function(doc) {\n\nif(doc.user_identifier == '2111')\n\t{\n\temit([doc.completed,doc.form_identifier], doc);\n  \n\t}\n}"
    }
}
}