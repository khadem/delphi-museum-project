package museum.delphi;

import java.util.ArrayList;
import java.util.HashMap;
import org.w3c.dom.Node;

public class TaxoNode {
	static int nextID = 1;		// used to synthesize ID values

	public String name;						// Called "id" in facetmap schema
	public String displayName;				// "title" in facetmap, unused in keyword tree
	public String controlName;				// From TMS - used to map to parents
	public int id;							// Becomes categoryid in DB
	public int termID;						// As per TMS
	public int facetid;						// With which facet is this associated?
	public TaxoNode parent;					// There is only one parent for now
	public ArrayList<TaxoNode> children;	// No children until specified
	public String sort;						// null if no sort specified
	public ArrayList<String> synset;	// Synonyms/alt terms for this node
	public ArrayList<String> exclset;	// Synonyms/alt terms for this node
	// Need to establish which namespace this uses - name or controlName
	// TODO - support both name list as well as node list. Add method
	// that takes a DoubleHashTree and resolves the implied nodes.
	// Change format to be facetName qualified, as in Color:Blue.
	// Will call DoubleHashTree.FindNodeByName( String facetName, String name );
	public ArrayList<String> impliedNodes;	// Other than ascendants
	public boolean inferredByChildren;// Is this node implied by children
	public boolean isGuideTerm;				// Is this node implied by children
	public boolean selectSingle;			// For facet map UI
	public int iMaskBase;					// First mask for this
	public int nMasks;						// How many masks does this subtree need
	public int iBitInMask;					// bit number for this node
	public Node elementNode;				// Used when working with XML and this

	protected int _debugLevel = 2;

	// Simple constructor uses defaults for synset and inferredByChildren
	// This is for the trivial keyword tree case
	public TaxoNode( String name, int termID, TaxoNode parent, String controlName, boolean isGuide ) {
		// No displayname, facetid or sort, and default values for others
		this( name, null, controlName, -1, termID, -1, parent, null, isGuide, true, false );
	}

	// Complex constructor for categories
	public TaxoNode(
			String		name,
			String		displayName,
			String		controlName,
			int				id,
			int				termID,
			int				facetid,
			TaxoNode	parent,
			String		sort,
			boolean		isGuide,
			boolean		inferredByChildren,
			boolean		selectSingle ) {
		this.name = name;
		this.displayName = displayName;
		this.controlName = controlName;
		if( id < 0 )
			id = nextID++;
		this.id= id;
		this.termID= termID;
		this.facetid= facetid;		// Only applies to categories
		this.parent = parent;
		this.sort = sort;
		this.isGuideTerm = isGuide;
		this.inferredByChildren = inferredByChildren;
		this.selectSingle = selectSingle;
		children = null;
		synset = null; 		// No syns unless specified
		exclset = null; 	// No exclusions unless specified
		impliedNodes = null; 	// No implied (other than parent) unless specified
		iMaskBase = -1;
		nMasks = 0;
		iBitInMask = -1;
	}

	protected void debug( int level, String str ){
		if( level <= _debugLevel )
			System.out.println( str );
	}

	public boolean isRoot() {
		return parent==null;
	}

	public void AddBitMaskAssignment( int iMaskToSet, int iBitInMaskToSet,
										boolean verbose ) {
		if( iMaskBase >= 0 ) {
			if(( iMaskToSet < (iMaskBase+nMasks) )
					|| ( iMaskToSet > (iMaskBase+nMasks) ))
				throw new RuntimeException(
						"Trying to set conflicting mask info for: " + name
						+ " MaskBase is: " + iMaskBase
						+ " nMasks : " + nMasks
						+ " trying to set: " + iMaskToSet );
			if( iBitInMask != iBitInMaskToSet )
				throw new RuntimeException(
						"Trying to set conflicting bit in mask info for: " + name
						+ " Prev is: " + iBitInMask
						+ " new is : " + iBitInMaskToSet );
			// So we just need to add one to the mask
			nMasks++;
			if( verbose )
				System.out.printf( "Added mask info for ["+name+"] nmasks:["
					+ nMasks+"]\n" );
		}
		else {
			iMaskBase = iMaskToSet;
			nMasks = 1;
			iBitInMask = iBitInMaskToSet;
			if( verbose )
				System.out.printf( "Set mask info for ["+name+"] mask:["
					+iMaskBase+"] bit: " + iBitInMask+"\n" );
		}
	}

	public String GetPathToNode() {
		if( parent == null )
			return "/" + name;
		else
			return parent.GetPathToNode() + "/" + name;
	}

	boolean AddSynonym( String synname ) {
		if( synset == null ) {
			synset = new ArrayList<String>(4);
		} else if( synset.contains(synname)) {
			return false;
		}
		synset.add(synname);
		return true;
	}

	boolean AddExclusion( String exclname ) {
		if( exclset == null ) {
			exclset = new ArrayList<String>(2);
		} else if( exclset.contains(exclname)) {
			return false;
		}
		exclset.add( exclname );
		return true;
	}

	void AddChild( TaxoNode child ) {
		if( children == null )
			children = new ArrayList<TaxoNode>();
		children.add( child );
		if( child.parent != this )
			System.out.println( "Added child that is not pointing to parent!!!");
	}

	public int GetNumDescendents() {
		int nDesc = 0;
		if(( children != null ) && ( children.size() > 0 ))
			for( int i=0; i<children.size(); i++ ) {
				TaxoNode child = children.get(i);
				nDesc++;	// One for the node, then add descendents
				nDesc += child.GetNumDescendents();
			}
		return nDesc;
	}

	// Add another reference to a category that this category infers. We accept
	// a path to assist in disambiguation. Path is rooted at a facet.
	// Path may also be a simple id if that is unambiguous within the facet.
	// TODO: Decide if we can allow cross-facet inferral.
	boolean AddImpliedNode( String path ) {
		if( impliedNodes == null )
			impliedNodes = new ArrayList<String>();
		// Check to see if there already, and if so, return false
		impliedNodes.add( path );
		return true;
	}

	public String toString(){
		String str = "TaxoNode{ "+name+", tID:"+termID+", "+controlName;
		if(parent!=null)
			str += ", parent:"+parent.termID;
		str += "}";
		return str;
	}

	// This checks whether either node is an ascendant of the other.
	// Returns 0 if the nodes are not related in the tree
	// Returns -1 if tag1 is an ascendant of tag2
	// Returns 1 if tag2 is an ascendant of tag1
	public static int CmpAscendancy( TaxoNode node1, TaxoNode node2 ) {
		if( node1 == null || node2 == null )
			throw new RuntimeException( "Bad node passed to CmpAscendancy" );
		TaxoNode par = node1.parent;
		while( par != null ) {
			if( par == node2 )
				return 1;  // Tag 2 is ascendant of tag1
			par = par.parent;
		}
		// Okay, so tag2 is not above tag1. Look the other way
		par = node2.parent;
		while( par != null ) {
			if( par == node1 )
				return -1;  // Tag1 is ascendant of tag2
			par = par.parent;
		}
		// If we got here, the nodes are not related
		return 0;
	} // CmpAscendancy

	public void AddInferredNodes(ArrayList<TaxoNode> list) {
		// while parents not null and inferredByChidren, add to list.
		// If find a node already in list, can assume we're done, since its
		// inferreds must also already be there
		// TODO include the implies nodes for each node, and not just ascendants
		TaxoNode ascendant = parent;
		while( ascendant != null && ascendant.inferredByChildren
				&& !list.contains(ascendant)) {
			list.add(ascendant);
			ascendant = ascendant.parent;
		}
	}

	public void AddInferredNodes(HashMap<TaxoNode, Float> map, float minValue ) {
		// while parents not null and inferredByChidren, add to list.
		// If find a node already in list, can assume we're done, since its
		// inferreds must also already be there
		// TODO include the implies nodes for each node, and not just ascendants
		TaxoNode ascendant = parent;
		while( ascendant != null && ascendant.inferredByChildren ) {
			Float ret = map.get(ascendant); // returns null if not in hashMap
			float priorVal = (ret==null)?0:ret.floatValue();
			if( minValue > priorVal )
				map.put(ascendant, minValue);
			else
				break;	// If found and higher value, can quit now.
			ascendant = ascendant.parent;
		}
	}

} // TreeNode class

