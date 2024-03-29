@REM if in OVERWRITE mode (-o), then toss the current file, if any
@IF EXIST mainVocab.xml (
@IF -%1 == --o @DEL /f /p mainVocab.xml
)
@IF EXIST mainVocab.xml (
  @ECHO File already exists. Delete or specify -o to overwrite
) ELSE (
@REM Put out the boilerplate for the facetmap
@cat MainVocabHeader.txt > mainVocab.xml
@REM
@REM Add each of the facets - EDIT HERE TO ADD MORE
@REM
@cat ObjectName_facet.xml Collection_facet.xml Activity_facet.xml Location_facet.xml Culture_facet.xml Materials_facet.xml TechniqueDesignDecoration_facet.xml Color_facet.xml | sed -e "/<?xml/d" -e "s/\t/ /g" >> mainVocab.xml
@REM
@REM Put out the ending boilerplate for the facetmap
@cat MainVocabFooter.txt >> mainVocab.xml
@REM
@REM Make it read-only to disincent editing by hand
@attrib +r mainVocab.xml
@ECHO Done.
)
